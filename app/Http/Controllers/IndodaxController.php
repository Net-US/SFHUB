<?php

namespace App\Http\Controllers;

use App\Models\IndodaxConnection;
use App\Services\IndodaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndodaxController extends Controller
{
    private const PROVIDER = 'indodax';

    /**
     * Simpan atau update Indodax API credentials
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string|max:255',
            'api_secret' => 'required|string',
        ]);

        $connection = IndodaxConnection::updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => self::PROVIDER],
            [
                'api_key' => $request->api_key,
                'api_secret' => $request->api_secret,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Koneksi Indodax berhasil disimpan.',
            'connection' => [
                'id' => $connection->id,
                'api_key' => substr($connection->api_key, 0, 8) . '...',
                'is_active' => $connection->is_active,
                'last_synced_at' => $connection->last_synced_at?->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Test koneksi Indodax API
     */
    public function test(): JsonResponse
    {
        $connection = IndodaxConnection::where('user_id', Auth::id())
            ->where('provider', self::PROVIDER)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada koneksi Indodax. Silakan isi API Key & Secret dulu.',
            ], 404);
        }

        $service = new IndodaxService($connection);
        $result = $service->testConnection();

        return response()->json($result);
    }

    /**
     * Sync balances dari Indodax ke database
     */
    public function sync(): JsonResponse
    {
        $connection = IndodaxConnection::where('user_id', Auth::id())
            ->where('provider', self::PROVIDER)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada koneksi Indodax.',
            ], 404);
        }

        $service = new IndodaxService($connection);
        $result = $service->syncBalances();

        return response()->json($result);
    }

    /**
     * Get status koneksi Indodax user
     */
    public function status(): JsonResponse
    {
        $connection = IndodaxConnection::where('user_id', Auth::id())
            ->where('provider', self::PROVIDER)
            ->first();

        if (!$connection) {
            return response()->json([
                'connected' => false,
                'message' => 'Belum terhubung ke Indodax.',
            ]);
        }

        return response()->json([
            'connected' => true,
            'api_key_preview' => substr($connection->api_key, 0, 12) . '...',
            'is_active' => $connection->is_active,
            'last_synced_at' => $connection->last_synced_at?->format('Y-m-d H:i:s'),
            'sync_status' => $connection->sync_status,
        ]);
    }

    /**
     * Disconnect (hapus koneksi Indodax)
     */
    public function disconnect(): JsonResponse
    {
        $deleted = IndodaxConnection::where('user_id', Auth::id())
            ->where('provider', self::PROVIDER)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada koneksi yang dihapus.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Koneksi Indodax berhasil diputus.',
        ]);
    }
}
