<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();
        $level = $request->get('level', 'all'); // Add this line

        if ($request->has('action')) {
            $query->where('action', $request->get('action'));
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->get('from_date'));
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->get('to_date'));
        }

        $activityLogs = $query->paginate($request->get('per_page', 50));

        // System logs
        $systemQuery = SystemLog::latest('logged_at');
        if ($request->has('level')) {
            $systemQuery->where('level', $request->get('level'));
        }
        $systemLogs = $systemQuery->take(100)->get();

        // Stats
        $totalLogs = ActivityLog::count();
        $errorLogs = SystemLog::where('level', 'error')->count();
        $warningLogs = SystemLog::where('level', 'warning')->count();
        $todayLogs = ActivityLog::whereDate('created_at', today())->count();

        return view('admin.logs', compact('activityLogs', 'systemLogs', 'totalLogs', 'errorLogs', 'warningLogs', 'todayLogs', 'level'));
    }

    public function getActivityLogs(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->has('action')) {
            $query->where('action', $request->get('action'));
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->get('from_date'));
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->get('to_date'));
        }

        $logs = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'data' => $logs->map(fn($log) => [
                'id' => $log->id,
                'user' => $log->user?->name ?? 'System',
                'action' => $log->action,
                'entity_type' => $log->entity_type,
                'entity_id' => $log->entity_id,
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'timestamp' => $log->created_at->format('Y-m-d H:i:s'),
            ]),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    public function getSystemLogs(Request $request): JsonResponse
    {
        $query = SystemLog::latest('logged_at');

        if ($request->has('level')) {
            $query->where('level', $request->get('level'));
        }

        if ($request->has('channel')) {
            $query->where('channel', $request->get('channel'));
        }

        $logs = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'data' => $logs->map(fn($log) => [
                'id' => $log->id,
                'level' => $log->level,
                'channel' => $log->channel,
                'message' => $log->message,
                'context' => $log->context,
                'timestamp' => $log->logged_at->format('Y-m-d H:i:s'),
            ]),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    public function clearActivityLogs(): JsonResponse
    {
        $days = request()->get('days', 30);
        $deleted = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'message' => "{$deleted} old activity logs cleared successfully",
        ]);
    }

    public function clearSystemLogs(): JsonResponse
    {
        $days = request()->get('days', 30);
        $deleted = SystemLog::where('logged_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'message' => "{$deleted} old system logs cleared successfully",
        ]);
    }

    public function getLogStats(): JsonResponse
    {
        $stats = [
            'activity_logs_count' => ActivityLog::count(),
            'system_logs_count' => SystemLog::count(),
            'activity_by_action' => ActivityLog::select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->pluck('count', 'action'),
            'system_by_level' => SystemLog::select('level', DB::raw('count(*) as count'))
                ->groupBy('level')
                ->pluck('count', 'level'),
            'recent_errors' => SystemLog::where('level', 'error')
                ->latest('logged_at')
                ->take(5)
                ->get(['message', 'logged_at']),
        ];

        return response()->json($stats);
    }

    public function exportLogs(Request $request)
    {
        $logs = ActivityLog::with('user')->latest()->get();

        $csv = "ID,User,Action,Entity,IP Address,Details,Created At\n";
        foreach ($logs as $log) {
            $userName = $log->user ? $log->user->name : 'System';
            $entityType = $log->entity_type ?? '-';
            $ipAddress = $log->ip_address ?? '-';
            $details = $log->details ?? '-';
            $details = str_replace('"', '""', $details);

            $csv .= "{$log->id},\"{$userName}\",{$log->action},{$entityType},{$ipAddress},\"{$details}\",{$log->created_at->format('Y-m-d H:i:s')}\n";
        }

        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function clearAllLogs(Request $request): JsonResponse
    {
        // Clear both activity and system logs
        ActivityLog::truncate();
        SystemLog::truncate();

        return response()->json([
            'message' => 'All logs cleared successfully',
        ]);
    }
}
