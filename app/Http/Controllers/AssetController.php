<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\FinanceAccount;
use App\Models\InvestmentInstrument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    private function ownAsset(int $id): Asset
    {
        return Asset::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    // ── INDEX ──────────────────────────────────────────────────────────
    public function index()
    {
        $userId = Auth::id();

        // FIX: orderBy('category') diganti orderBy('name')
        // karena kolom 'category' mungkin belum ada di migration lama.
        // Kalau migration Anda sudah punya kolom 'category', ganti balik ke:
        //   ->orderBy('category')
        $assets = Asset::where('user_id', $userId)
            ->orderBy('name')
            ->orderByDesc('current_value')
            ->get();

        $accounts = FinanceAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('type')
            ->get();

        $totalPurchasePrice = $assets->sum('purchase_price');
        $totalCurrentValue  = $assets->sum('current_value');
        $totalAppreciation  = $totalCurrentValue - $totalPurchasePrice;

        $totalCash        = $accounts->where('type', 'cash')->sum('balance');
        $totalBank        = $accounts->where('type', 'bank')->sum('balance');
        $totalWallet      = $accounts->where('type', 'e-wallet')->sum('balance');
        $totalInvestment  = $accounts->where('type', 'investment')->sum('balance');
        $totalReceivable  = $accounts->where('type', 'receivable')->sum('balance');
        $totalAllAccounts = $accounts->sum('balance');

        // Group aset per category (fallback ke 'type' atau 'Lainnya')
        $assetsByCategory = $assets->groupBy(function ($a) {
            return $a->category ?? ($a->type ?? 'Lainnya');
        });

        $warrantyAlerts  = $assets->filter(fn($a) => $a->isWarrantyExpiringSoon(30));
        $insuranceAlerts = $assets->filter(fn($a) => $a->isInsuranceExpiringSoon(30));

        $indodaxAccount = $accounts->first(function ($account) {
            return $account->type === 'investment' && strtolower($account->name) === 'indodax';
        });

        $indodaxInstruments = collect();
        if ($indodaxAccount) {
            $indodaxInstruments = InvestmentInstrument::where('user_id', $userId)
                ->where('type', 'crypto')
                ->where('finance_account_id', $indodaxAccount->id)
                ->orderByDesc('total_quantity')
                ->get();
        }

        $indodaxTotalValue = $indodaxInstruments->sum(function ($instrument) {
            return (float) $instrument->total_quantity * (float) $instrument->current_price;
        });

        return view('dashboard.assets', compact(
            'assets',
            'accounts',
            'totalPurchasePrice',
            'totalCurrentValue',
            'totalAppreciation',
            'totalCash',
            'totalBank',
            'totalWallet',
            'totalInvestment',
            'totalReceivable',
            'totalAllAccounts',
            'assetsByCategory',
            'warrantyAlerts',
            'insuranceAlerts',
            'indodaxAccount',
            'indodaxInstruments',
            'indodaxTotalValue',
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'category'         => 'nullable|string|max:50',
            'purchase_price'   => 'required|numeric|min:0',
            'current_value'    => 'required|numeric|min:0',
            'purchase_date'    => 'required|date',
            'condition'        => 'required|in:Excellent,Good,Fair,Poor',
            'description'      => 'nullable|string|max:500',
            'notes'            => 'nullable|string|max:500',
            'location'         => 'nullable|string|max:100',
            'serial_number'    => 'nullable|string|max:100',
            'warranty_expiry'  => 'nullable|date',
            'is_insured'       => 'boolean',
            'insurance_expiry' => 'nullable|date',
        ]);

        $data['user_id']    = Auth::id();
        $data['is_insured'] = $data['is_insured'] ?? false;

        $asset = Asset::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Aset berhasil ditambahkan.',
            'asset'   => $this->formatAsset($asset),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $asset = $this->ownAsset($id);

        $data = $request->validate([
            'name'             => 'sometimes|required|string|max:100',
            'category'         => 'nullable|string|max:50',
            'purchase_price'   => 'sometimes|required|numeric|min:0',
            'current_value'    => 'sometimes|required|numeric|min:0',
            'purchase_date'    => 'sometimes|required|date',
            'condition'        => 'sometimes|required|in:Excellent,Good,Fair,Poor',
            'description'      => 'nullable|string|max:500',
            'notes'            => 'nullable|string|max:500',
            'location'         => 'nullable|string|max:100',
            'serial_number'    => 'nullable|string|max:100',
            'warranty_expiry'  => 'nullable|date',
            'is_insured'       => 'boolean',
            'insurance_expiry' => 'nullable|date',
        ]);

        $asset->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Aset berhasil diupdate.',
            'asset'   => $this->formatAsset($asset->fresh()),
        ]);
    }

    public function updateValue(Request $request, int $id)
    {
        $asset = $this->ownAsset($id);
        $asset->update($request->validate(['current_value' => 'required|numeric|min:0']));
        $fresh = $asset->fresh();

        return response()->json([
            'success'          => true,
            'message'          => 'Nilai aset diupdate.',
            'current_value'    => (float) $fresh->current_value,
            'appreciation'     => $fresh->getAppreciation(),
            'appreciation_pct' => round($fresh->getAppreciationPercentage(), 2),
        ]);
    }

    public function destroy(int $id)
    {
        $this->ownAsset($id)->delete();
        return response()->json(['success' => true, 'message' => 'Aset berhasil dihapus.']);
    }

    public function show(int $id)
    {
        return response()->json(['success' => true, 'asset' => $this->formatAsset($this->ownAsset($id))]);
    }

    public function getSummary()
    {
        $userId   = Auth::id();
        $assets   = Asset::where('user_id', $userId)->get();
        $accounts = FinanceAccount::where('user_id', $userId)->where('is_active', true)->get();

        return response()->json([
            'physical_assets' => [
                'total_purchase'     => (float) $assets->sum('purchase_price'),
                'total_current'      => (float) $assets->sum('current_value'),
                'total_appreciation' => (float) ($assets->sum('current_value') - $assets->sum('purchase_price')),
                'count'              => $assets->count(),
            ],
            'accounts' => [
                'cash'       => (float) $accounts->where('type', 'cash')->sum('balance'),
                'bank'       => (float) $accounts->where('type', 'bank')->sum('balance'),
                'e_wallet'   => (float) $accounts->where('type', 'e-wallet')->sum('balance'),
                'investment' => (float) $accounts->where('type', 'investment')->sum('balance'),
                'receivable' => (float) $accounts->where('type', 'receivable')->sum('balance'),
                'total'      => (float) $accounts->sum('balance'),
            ],
        ]);
    }


    // ── UPDATE AKUN (nama, warna, catatan, status) ────────────────────
    public function updateAccount(Request $request, int $id)
    {
        $account = FinanceAccount::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'color'          => 'nullable|string|max:20',
            'notes'          => 'nullable|string|max:500',
            'is_active'      => 'boolean',
        ]);

        $account->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diupdate.',
            'account' => $this->formatAccount($account->fresh()),
        ]);
    }

    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'type'           => 'required|in:cash,bank,e-wallet,investment,receivable',
            'account_number' => 'nullable|string|max:50',
            'balance'        => 'required|numeric|min:0',
            'color'          => 'nullable|string|max:20',
            'notes'          => 'nullable|string|max:500',
        ]);

        $data['user_id']  = Auth::id();
        $data['currency'] = 'IDR';
        $data['color']    = $data['color'] ?? '#6b7280';

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil ditambahkan.',
            'account' => $this->formatAccount(FinanceAccount::create($data)),
        ]);
    }

    public function updateAccountBalance(Request $request, int $id)
    {
        $account = FinanceAccount::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $account->update($request->validate(['balance' => 'required|numeric|min:0']));

        return response()->json([
            'success'     => true,
            'message'     => 'Saldo berhasil diupdate.',
            'new_balance' => (float) $account->fresh()->balance,
        ]);
    }

    public function destroyAccount(int $id)
    {
        $account = FinanceAccount::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($account->transactions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak bisa dihapus karena sudah ada transaksi.',
            ], 422);
        }

        $account->delete();
        return response()->json(['success' => true, 'message' => 'Akun berhasil dihapus.']);
    }

    private function formatAsset(Asset $a): array
    {
        return [
            'id'                   => $a->id,
            'name'                 => $a->name,
            'category'             => $a->category ?? $a->type ?? null,
            'purchase_price'       => (float) $a->purchase_price,
            'purchase_price_fmt'   => $a->getFormattedPurchasePrice(),
            'current_value'        => (float) $a->current_value,
            'current_value_fmt'    => $a->getFormattedCurrentValue(),
            'appreciation'         => $a->getAppreciation(),
            'appreciation_pct'     => round($a->getAppreciationPercentage(), 2),
            'appreciation_color'   => $a->getStatusColor(),
            'purchase_date'        => $a->purchase_date?->format('Y-m-d'),
            'purchase_date_fmt'    => $a->purchase_date?->isoFormat('D MMM YYYY'),
            'condition'            => $a->condition,
            'condition_color'      => $a->getConditionColor(),
            'description'          => $a->description,
            'notes'                => $a->notes,
            'location'             => $a->location,
            'serial_number'        => $a->serial_number,
            'warranty_expiry'      => $a->warranty_expiry?->format('Y-m-d'),
            'warranty_expiry_fmt'  => $a->warranty_expiry?->isoFormat('D MMM YYYY'),
            'warranty_alert'       => $a->isWarrantyExpiringSoon(30),
            'is_insured'           => $a->is_insured,
            'insurance_expiry'     => $a->insurance_expiry?->format('Y-m-d'),
            'insurance_expiry_fmt' => $a->insurance_expiry?->isoFormat('D MMM YYYY'),
            'insurance_alert'      => $a->isInsuranceExpiringSoon(30),
            'age_days'             => $a->getAgeInDays(),
        ];
    }

    private function formatAccount(FinanceAccount $a): array
    {
        return [
            'id'             => $a->id,
            'name'           => $a->name,
            'type'           => $a->type,
            'type_label'     => $a->getTypeLabel(),
            'type_icon'      => $a->getTypeIcon(),
            'account_number' => $a->account_number,
            'balance'        => (float) $a->balance,
            'balance_fmt'    => $a->getFormattedBalance(),
            'color'          => $a->color,
            'notes'          => $a->notes,
            'is_active'      => $a->is_active,
        ];
    }
}
