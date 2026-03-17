<?php

namespace App\Http\Controllers;

use App\Models\InvestmentInstrument;
use App\Models\InvestmentPurchase;
use App\Models\FinanceAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvestmentController extends Controller
{
    private function ownInstrument(int $id): InvestmentInstrument
    {
        return InvestmentInstrument::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    private function ownPurchase(int $id): InvestmentPurchase
    {
        return InvestmentPurchase::whereHas(
            'instrument',
            fn($q) => $q->where('user_id', Auth::id())
        )->where('id', $id)->firstOrFail();
    }

    // ── INDEX ──────────────────────────────────────────────────────────
    public function index()
    {
        $userId = Auth::id();

        $instruments = InvestmentInstrument::where('user_id', $userId)
            ->with(['purchases', 'financeAccount'])
            ->orderBy('type')
            ->orderByDesc('total_invested')
            ->get();

        // Akun keuangan tipe 'investment' milik user ini
        // (Indodax, Ajaib, Bibit, dll)
        $investmentAccounts = FinanceAccount::where('user_id', $userId)
            ->where('type', 'investment')
            ->where('is_active', true)
            ->get();

        // ── Ringkasan portofolio ───────────────────────────────
        $totalInvested     = $instruments->sum('total_invested');
        $totalCurrentValue = $instruments->sum(fn($i) => $i->getCurrentValue());
        $totalPL           = $totalCurrentValue - $totalInvested;
        $totalPLPct        = $totalInvested > 0
            ? round(($totalPL / $totalInvested) * 100, 2)
            : 0;

        // ── Breakdown per tipe ─────────────────────────────────
        $byType = $instruments->groupBy('type')->map(fn($group) => [
            'count'          => $group->count(),
            'total_invested' => (float) $group->sum('total_invested'),
            'current_value'  => (float) $group->sum(fn($i) => $i->getCurrentValue()),
            'profit_loss'    => (float) ($group->sum(fn($i) => $i->getCurrentValue()) - $group->sum('total_invested')),
        ]);

        // ── Breakdown per akun/platform ─────────────────────────
        // Ini yang menghubungkan "saldo Indodax" ke instrumen di dalamnya
        $byAccount = $instruments
            ->filter(fn($i) => $i->finance_account_id !== null)
            ->groupBy('finance_account_id')
            ->map(function ($group, $accountId) use ($investmentAccounts) {
                $account = $investmentAccounts->find($accountId);
                return [
                    'account_id'     => $accountId,
                    'account_name'   => $account?->name ?? 'Platform Tidak Diketahui',
                    'account_color'  => $account?->color ?? '#6b7280',
                    'account_balance' => (float) ($account?->balance ?? 0),
                    'total_invested' => (float) $group->sum('total_invested'),
                    'current_value'  => (float) $group->sum(fn($i) => $i->getCurrentValue()),
                    'profit_loss'    => (float) ($group->sum(fn($i) => $i->getCurrentValue()) - $group->sum('total_invested')),
                    'instruments'    => $group->map(fn($i) => [
                        'id'           => $i->id,
                        'name'         => $i->name,
                        'symbol'       => $i->symbol,
                        'current_value' => (float) $i->getCurrentValue(),
                        'profit_loss'  => (float) $i->getProfitLoss(),
                        'profit_loss_pct' => round($i->getProfitLossPercentage(), 2),
                    ])->values(),
                ];
            })->values();

        // Instrumen tanpa akun (belum dikaitkan ke platform)
        $unlinkedInstruments = $instruments->filter(fn($i) => $i->finance_account_id === null);

        $sortedByPct    = $instruments->sortByDesc(fn($i) => $i->getProfitLossPercentage());
        $bestPerformer  = $sortedByPct->first();
        $worstPerformer = $sortedByPct->last();

        $recentPurchases = InvestmentPurchase::whereHas(
            'instrument',
            fn($q) => $q->where('user_id', $userId)
        )->with('instrument')
            ->orderByDesc('purchase_date')
            ->limit(8)
            ->get();

        return view('dashboard.investments', compact(
            'instruments',
            'investmentAccounts',
            'totalInvested',
            'totalCurrentValue',
            'totalPL',
            'totalPLPct',
            'byType',
            'byAccount',
            'unlinkedInstruments',
            'bestPerformer',
            'worstPerformer',
            'recentPurchases',
        ));
    }

    // ── STORE INSTRUMENT ──────────────────────────────────────────────
    public function storeInstrument(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'symbol'             => 'required|string|max:20',
            'type'               => 'required|in:crypto,stocks,mutual-fund,gold,bonds,etf,other',
            'current_price'      => 'required|numeric|min:0',
            'finance_account_id' => 'nullable|integer',
            'notes'              => 'nullable|string|max:500',
            // ── Setup Awal (kondisi saat ini, opsional) ───────────────────
            // User tidak tahu riwayat pembelian tapi tahu kondisi sekarang:
            // berapa uang yang sudah diinvestasikan & berapa unit yang dimiliki
            'initial_value'    => 'nullable|numeric|min:0',   // nilai investasi saat ini (Rp)
            'initial_quantity' => 'nullable|numeric|min:0',   // jumlah unit/koin yang dimiliki
            'initial_return_pct' => 'nullable|numeric',       // return % (opsional, untuk hitung modal)
        ]);

        if (!empty($data['finance_account_id'])) {
            FinanceAccount::where('id', $data['finance_account_id'])
                ->where('user_id', Auth::id())
                ->where('type', 'investment')
                ->firstOrFail();
        }

        $data['user_id'] = Auth::id();
        $data['symbol']  = strtoupper($data['symbol']);

        // ── Hitung total_invested & total_quantity dari setup awal ────────
        // Logika: user tahu nilai sekarang dan return %, bisa hitung modal.
        // Jika tidak ada return % → anggap modal = nilai sekarang (breakeven).
        $initialValue    = (float) ($data['initial_value']    ?? 0);
        $initialQuantity = (float) ($data['initial_quantity'] ?? 0);
        $returnPct       = isset($data['initial_return_pct']) ? (float) $data['initial_return_pct'] : null;
        $currentPrice    = (float) $data['current_price'];

        if ($initialValue > 0 || $initialQuantity > 0) {
            // Hitung modal dari return %: modal = nilai_sekarang / (1 + return/100)
            if ($returnPct !== null && $returnPct != 0) {
                $computedModal = $initialValue / (1 + $returnPct / 100);
            } elseif ($initialValue > 0) {
                // Tidak tahu return → anggap modal = nilai sekarang (return 0%)
                $computedModal = $initialValue;
            } else {
                $computedModal = 0;
            }

            // Hitung quantity dari current_price jika tidak diisi
            $computedQty = $initialQuantity > 0
                ? $initialQuantity
                : ($currentPrice > 0 ? $initialValue / $currentPrice : 0);

            $data['total_invested'] = round($computedModal, 2);
            $data['total_quantity'] = round($computedQty, 8);
            $data['average_price']  = $computedQty > 0
                ? round($computedModal / $computedQty, 8)
                : 0;

            // Buat satu record pembelian "Setup Awal" sebagai referensi historis
            // Ini bukan pembelian nyata, hanya penanda kondisi awal
            $instrument = InvestmentInstrument::create([
                'user_id'            => $data['user_id'],
                'finance_account_id' => $data['finance_account_id'] ?? null,
                'name'               => $data['name'],
                'symbol'             => $data['symbol'],
                'type'               => $data['type'],
                'current_price'      => $currentPrice,
                'total_invested'     => $data['total_invested'],
                'total_quantity'     => $data['total_quantity'],
                'average_price'      => $data['average_price'],
                'notes'              => $data['notes'] ?? null,
            ]);

            // Catat sebagai purchase "Setup Awal" jika ada data
            if ($data['total_invested'] > 0 || $data['total_quantity'] > 0) {
                $instrument->purchases()->create([
                    'instrument_id'  => $instrument->id,
                    'purchase_date'  => now()->toDateString(),
                    'amount'         => $data['total_invested'],
                    'quantity'       => $data['total_quantity'],
                    'price_per_unit' => $data['average_price'],
                    'fees'           => 0,
                    'notes'          => 'Setup Awal — kondisi portfolio saat pertama kali dicatat',
                ]);
            }
        } else {
            // Tidak ada setup awal → instrumen baru, belum ada posisi
            $instrument = InvestmentInstrument::create([
                'user_id'            => $data['user_id'],
                'finance_account_id' => $data['finance_account_id'] ?? null,
                'name'               => $data['name'],
                'symbol'             => $data['symbol'],
                'type'               => $data['type'],
                'current_price'      => $currentPrice,
                'total_invested'     => 0,
                'total_quantity'     => 0,
                'average_price'      => 0,
                'notes'              => $data['notes'] ?? null,
            ]);
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Instrumen berhasil ditambahkan.' . ($initialValue > 0 ? ' Setup awal dicatat.' : ''),
            'instrument' => $this->formatInstrument($instrument->load(['purchases', 'financeAccount'])),
        ]);
    }

    /**
     * Update kondisi saat ini secara manual (jika user lupa update atau ingin
     * koreksi total_invested/quantity tanpa menghapus history pembelian).
     * Mirip "edit saldo" di rekening bank.
     */
    public function updatePosition(Request $request, int $id)
    {
        $instrument = $this->ownInstrument($id);

        $data = $request->validate([
            'total_invested' => 'required|numeric|min:0',
            'total_quantity' => 'required|numeric|min:0',
            'current_price'  => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:500',
        ]);

        // Hitung ulang average price
        $data['average_price'] = $data['total_quantity'] > 0
            ? round($data['total_invested'] / $data['total_quantity'], 8)
            : 0;

        $instrument->update($data);

        $fresh = $instrument->fresh();

        return response()->json([
            'success'    => true,
            'message'    => 'Posisi berhasil diupdate.',
            'instrument' => $this->formatInstrument($fresh->load(['purchases', 'financeAccount'])),
        ]);
    }

    // ── UPDATE INSTRUMENT ─────────────────────────────────────────────
    public function updateInstrument(Request $request, int $id)
    {
        $instrument = $this->ownInstrument($id);

        $data = $request->validate([
            'name'               => 'sometimes|required|string|max:100',
            'notes'              => 'nullable|string|max:500',
            'current_price'      => 'sometimes|required|numeric|min:0',
            'finance_account_id' => 'nullable|integer',
        ]);

        if (!empty($data['finance_account_id'])) {
            FinanceAccount::where('id', $data['finance_account_id'])
                ->where('user_id', Auth::id())
                ->where('type', 'investment')
                ->firstOrFail();
        }

        $instrument->update($data);

        return response()->json([
            'success'    => true,
            'message'    => 'Instrumen berhasil diupdate.',
            'instrument' => $this->formatInstrument($instrument->fresh()->load(['purchases', 'financeAccount'])),
        ]);
    }

    // ── UPDATE PRICE ──────────────────────────────────────────────────
    public function updatePrice(Request $request, int $id)
    {
        $instrument = $this->ownInstrument($id);
        $instrument->update($request->validate(['current_price' => 'required|numeric|min:0']));
        $fresh = $instrument->fresh();

        return response()->json([
            'success'         => true,
            'message'         => 'Harga berhasil diupdate.',
            'current_price'   => (float) $fresh->current_price,
            'current_value'   => (float) $fresh->getCurrentValue(),
            'profit_loss'     => (float) $fresh->getProfitLoss(),
            'profit_loss_pct' => round($fresh->getProfitLossPercentage(), 2),
        ]);
    }

    // ── DESTROY INSTRUMENT ────────────────────────────────────────────
    public function destroyInstrument(int $id)
    {
        $instrument = $this->ownInstrument($id);

        DB::transaction(function () use ($instrument) {
            $instrument->purchases()->delete();
            $instrument->delete();
        });

        return response()->json(['success' => true, 'message' => 'Instrumen berhasil dihapus.']);
    }

    // ── STORE PURCHASE ────────────────────────────────────────────────
    public function storePurchase(Request $request, int $instrumentId)
    {
        $instrument = $this->ownInstrument($instrumentId);

        $data = $request->validate([
            'purchase_date'  => 'required|date',
            'amount'         => 'required|numeric|min:1',
            'quantity'       => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'fees'           => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($instrument, $data) {
            $instrument->purchases()->create([
                'instrument_id'  => $instrument->id,
                'purchase_date'  => $data['purchase_date'],
                'amount'         => $data['amount'],
                'quantity'       => $data['quantity'],
                'price_per_unit' => $data['price_per_unit'],
                'fees'           => $data['fees'] ?? 0,
                'notes'          => $data['notes'] ?? null,
            ]);
            $instrument->updateAveragePrice();
        });

        return response()->json([
            'success'    => true,
            'message'    => 'Pembelian berhasil ditambahkan.',
            'instrument' => $this->formatInstrument($instrument->fresh()->load(['purchases', 'financeAccount'])),
        ]);
    }

    // ── DESTROY PURCHASE ──────────────────────────────────────────────
    public function destroyPurchase(int $purchaseId)
    {
        $purchase   = $this->ownPurchase($purchaseId);
        $instrument = $purchase->instrument;

        DB::transaction(function () use ($purchase, $instrument) {
            $purchase->delete();
            $instrument->updateAveragePrice();
        });

        return response()->json([
            'success'    => true,
            'message'    => 'Pembelian berhasil dihapus.',
            'instrument' => $this->formatInstrument($instrument->fresh()->load(['purchases', 'financeAccount'])),
        ]);
    }

    // ── SHOW INSTRUMENT ───────────────────────────────────────────────
    public function showInstrument(int $id)
    {
        $instrument = $this->ownInstrument($id);
        $instrument->load(['purchases', 'financeAccount']);

        return response()->json([
            'success'    => true,
            'instrument' => $this->formatInstrument($instrument),
        ]);
    }

    // ── SUMMARY (AJAX) ────────────────────────────────────────────────
    public function getSummary()
    {
        $userId      = Auth::id();
        $instruments = InvestmentInstrument::where('user_id', $userId)->with('purchases')->get();

        $totalInvested     = (float) $instruments->sum('total_invested');
        $totalCurrentValue = (float) $instruments->sum(fn($i) => $i->getCurrentValue());
        $totalPL           = $totalCurrentValue - $totalInvested;

        return response()->json([
            'summary' => [
                'total_invested'      => $totalInvested,
                'total_current_value' => $totalCurrentValue,
                'total_profit_loss'   => $totalPL,
                'total_pl_pct'        => $totalInvested > 0 ? round(($totalPL / $totalInvested) * 100, 2) : 0,
                'instrument_count'    => $instruments->count(),
            ],
            'instruments' => $instruments->map(fn($i) => $this->formatInstrument($i)),
        ]);
    }

    // ── FORMATTERS ────────────────────────────────────────────────────
    private function formatInstrument(InvestmentInstrument $i): array
    {
        $currentValue = (float) $i->getCurrentValue();
        $profitLoss   = (float) $i->getProfitLoss();
        $plPct        = round($i->getProfitLossPercentage(), 2);

        return [
            'id'                 => $i->id,
            'name'               => $i->name,
            'symbol'             => $i->symbol,
            'type'               => $i->type,
            'type_label'         => $this->typeLabel($i->type),
            'notes'              => $i->notes,
            // Platform/akun yang menampung instrumen ini
            'finance_account_id' => $i->finance_account_id,
            'account_name'       => $i->financeAccount?->name,
            'account_color'      => $i->financeAccount?->color,
            // Harga & nilai
            'current_price'      => (float) $i->current_price,
            'current_price_fmt'  => 'Rp ' . number_format($i->current_price, 0, ',', '.'),
            'total_invested'     => (float) $i->total_invested,
            'total_invested_fmt' => 'Rp ' . number_format($i->total_invested, 0, ',', '.'),
            'total_quantity'     => (float) $i->total_quantity,
            'average_price'      => (float) $i->average_price,
            'average_price_fmt'  => 'Rp ' . number_format($i->average_price, 0, ',', '.'),
            'current_value'      => $currentValue,
            'current_value_fmt'  => 'Rp ' . number_format($currentValue, 0, ',', '.'),
            'profit_loss'        => $profitLoss,
            'profit_loss_fmt'    => ($profitLoss >= 0 ? '+' : '') . 'Rp ' . number_format(abs($profitLoss), 0, ',', '.'),
            'profit_loss_pct'    => $plPct,
            'performance_color'  => $i->getPerformanceColor(),
            'purchases_count'    => $i->purchases->count(),
            'purchases'          => $i->purchases->map(fn($p) => $this->formatPurchase($p, $i))->toArray(),
        ];
    }

    private function formatPurchase(InvestmentPurchase $p, InvestmentInstrument $instrument): array
    {
        $currentValue = (float) ($p->quantity * $instrument->current_price);
        $profitLoss   = $currentValue - (float) $p->amount;
        $plPct        = $p->amount > 0 ? round(($profitLoss / $p->amount) * 100, 2) : 0;

        return [
            'id'                => $p->id,
            'purchase_date'     => $p->purchase_date?->format('Y-m-d'),
            'purchase_date_fmt' => $p->purchase_date?->isoFormat('D MMM YYYY'),
            'amount'            => (float) $p->amount,
            'amount_fmt'        => 'Rp ' . number_format($p->amount, 0, ',', '.'),
            'quantity'          => (float) $p->quantity,
            'price_per_unit'    => (float) $p->price_per_unit,
            'price_per_unit_fmt' => 'Rp ' . number_format($p->price_per_unit, 0, ',', '.'),
            'fees'              => (float) $p->fees,
            'fees_fmt'          => 'Rp ' . number_format($p->fees, 0, ',', '.'),
            'notes'             => $p->notes,
            'current_value'     => $currentValue,
            'current_value_fmt' => 'Rp ' . number_format($currentValue, 0, ',', '.'),
            'profit_loss'       => $profitLoss,
            'profit_loss_fmt'   => ($profitLoss >= 0 ? '+' : '') . 'Rp ' . number_format(abs($profitLoss), 0, ',', '.'),
            'profit_loss_pct'   => $plPct,
            'profit_loss_color' => $profitLoss >= 0 ? 'text-emerald-600' : 'text-rose-600',
        ];
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'crypto'      => 'Cryptocurrency',
            'stocks'      => 'Saham',
            'mutual-fund' => 'Reksadana',
            'gold'        => 'Emas',
            'bonds'       => 'Obligasi',
            'etf'         => 'ETF',
            default       => 'Lainnya',
        };
    }
}
