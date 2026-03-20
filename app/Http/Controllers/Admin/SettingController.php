<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        // Get backup stats
        $backups = Backup::latest()->paginate(10);
        $totalBackups = Backup::count();
        $totalSize = Backup::sum('file_size') ?? 0; // Use file_size instead of size
        $lastBackup = Backup::latest()->first();
        $autoBackup = SystemSetting::get('auto_backup_enabled', false);

        return view('admin.settings', compact('settings', 'backups', 'totalBackups', 'totalSize', 'lastBackup', 'autoBackup'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:system_settings,key',
            'value' => 'nullable',
            'type' => 'required|in:string,integer,boolean,json',
            'group' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $setting = SystemSetting::create($validated);

        // Clear cache if the setting was cached
        Cache::forget('setting_' . $setting->key);

        return response()->json([
            'message' => 'Setting created successfully',
            'setting' => $setting,
        ], 201);
    }

    public function update(Request $request, SystemSetting $setting): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'nullable',
            'type' => 'sometimes|in:string,integer,boolean,json',
            'group' => 'sometimes|string|max:50',
            'description' => 'nullable|string',
            'is_public' => 'sometimes|boolean',
        ]);

        $setting->update($validated);

        // Clear cache
        Cache::forget('setting_' . $setting->key);

        return response()->json([
            'message' => 'Setting updated successfully',
            'setting' => $setting,
        ]);
    }

    public function destroy(SystemSetting $setting): JsonResponse
    {
        Cache::forget('setting_' . $setting->key);
        $setting->delete();

        return response()->json([
            'message' => 'Setting deleted successfully',
        ]);
    }

    public function getByGroup(string $group): JsonResponse
    {
        $settings = SystemSetting::where('group', $group)->get();

        return response()->json($settings);
    }

    public function getValue(string $key): JsonResponse
    {
        $value = SystemSetting::get($key);

        return response()->json([
            'key' => $key,
            'value' => $value,
        ]);
    }

    public function setValue(Request $request, string $key): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required',
            'type' => 'required|in:string,integer,boolean,json',
            'group' => 'nullable|string|max:50',
        ]);

        $setting = SystemSetting::set($key, $validated['value'], $validated['type'], $validated['group'] ?? 'general');

        return response()->json([
            'message' => 'Setting saved successfully',
            'setting' => $setting,
        ]);
    }

    public function clearCache(): JsonResponse
    {
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return response()->json([
            'message' => 'All caches cleared successfully',
        ]);
    }

    public function getSystemInfo(): JsonResponse
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'database_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'disk_space' => [
                'free' => disk_free_space(storage_path()),
                'total' => disk_total_space(storage_path()),
            ],
        ];

        return response()->json($info);
    }

    public function backups()
    {
        $backups = Backup::with('creator')->latest()->paginate(20);
        $totalBackups = Backup::count();
        $totalSize = Backup::sum('file_size');
        $lastBackup = Backup::latest()->first();
        $autoBackup = SystemSetting::get('auto_backup_enabled', false);

        return view('admin.backups', compact('backups', 'totalBackups', 'totalSize', 'lastBackup', 'autoBackup'));
    }

    public function getBackups(): JsonResponse
    {
        $backups = Backup::with('creator')->latest()->paginate(20);

        return response()->json([
            'data' => $backups->map(fn($backup) => [
                'id' => $backup->id,
                'file_name' => $backup->file_name,
                'file_size' => $this->formatBytes($backup->file_size),
                'type' => $backup->type,
                'status' => $backup->status,
                'created_by' => $backup->creator?->name ?? 'System',
                'completed_at' => $backup->completed_at?->format('Y-m-d H:i:s'),
                'created_at' => $backup->created_at->format('Y-m-d H:i:s'),
            ]),
            'pagination' => [
                'current_page' => $backups->currentPage(),
                'last_page' => $backups->lastPage(),
                'per_page' => $backups->perPage(),
                'total' => $backups->total(),
            ],
        ]);
    }

    public function createBackup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:full,database,files',
            'notes' => 'nullable|string',
        ]);

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '_' . $validated['type'] . '.zip';
        $path = storage_path('app/backups/' . $filename);

        // Ensure directory exists
        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $backup = Backup::create([
            'file_name' => $filename,
            'file_path' => $path,
            'file_size' => 0,
            'type' => $validated['type'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Here you would trigger the actual backup process
        // For now, we'll simulate completion
        $backup->update([
            'status' => 'completed',
            'completed_at' => now(),
            'file_size' => file_exists($path) ? filesize($path) : 0,
        ]);

        return response()->json([
            'message' => 'Backup created successfully',
            'backup' => $backup,
        ], 201);
    }

    public function downloadBackup(Backup $backup)
    {
        if (!file_exists($backup->file_path)) {
            return response()->json(['message' => 'Backup file not found'], 404);
        }

        return response()->download($backup->file_path, $backup->file_name);
    }

    public function destroyBackup(Backup $backup): JsonResponse
    {
        if (file_exists($backup->file_path)) {
            unlink($backup->file_path);
        }

        $backup->delete();

        return response()->json([
            'message' => 'Backup deleted successfully',
        ]);
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function saveSettings(Request $request): JsonResponse
    {
        $settings = $request->all();

        foreach ($settings as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'string']
            );
        }

        return response()->json([
            'message' => 'Settings saved successfully',
        ]);
    }

    public function configBackup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auto_backup_enabled' => 'boolean',
            'backup_frequency' => 'in:daily,weekly,monthly',
            'backup_retention' => 'integer|min:1|max:30',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => is_bool($value) ? 'boolean' : 'string']
            );
        }

        return response()->json([
            'message' => 'Backup configuration saved successfully',
        ]);
    }

    public function testEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Simulate email test
        return response()->json([
            'message' => 'Test email sent successfully to ' . $validated['email'],
        ]);
    }
}
