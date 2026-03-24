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
        if ($this->shouldBypassForAuth($request)) {
            return $next($request);
        }

        if ($request->user()?->hasRole('admin')) {
            return $next($request);
        }

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

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'maintenance' => true,
                ], 503);
            }

            return response()->view('errors.maintenance', [
                'message' => $message,
            ], 503);
        }

        return $next($request);
    }

    private function shouldBypassForAuth(Request $request): bool
    {
        if ($request->is('login') || $request->is('register') || $request->is('password/*')) {
            return true;
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return true;
        }

        $bypassToken = SystemSetting::get('maintenance_bypass_token');

        return !empty($bypassToken)
            && hash_equals((string) $bypassToken, (string) $request->query('maintenance_bypass'));
    }
}
