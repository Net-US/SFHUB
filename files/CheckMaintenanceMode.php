<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        // Admin tetap bisa akses walaupun maintenance
        if ($request->user()?->hasRole('admin')) {
            return $next($request);
        }

        // Cek dari cache dulu (agar tidak query DB setiap request)
        $maintenanceMode = Cache::remember('maintenance_mode', 300, function () {
            return SystemSetting::get('maintenance_mode', false);
        });

        if ($maintenanceMode) {
            $message = Cache::remember('maintenance_message', 300, function () {
                return SystemSetting::get(
                    'maintenance_message',
                    'Kami sedang melakukan pemeliharaan. Silakan cek kembali nanti.'
                );
            });

            // Jika request API/AJAX, kembalikan JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'maintenance' => true,
                ], 503);
            }

            // Jika request web biasa, tampilkan halaman maintenance
            return response()->view('errors.maintenance', [
                'message' => $message,
            ], 503);
        }

        return $next($request);
    }
}
