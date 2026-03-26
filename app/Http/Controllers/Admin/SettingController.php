<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use ZipArchive;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key')->toArray();

        $settingsByGroup = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        // Backup stats
        $backups = Backup::latest()->paginate(10);
        $totalBackups = Backup::count();
        $totalSize = Backup::sum('file_size') ?? 0;
        $lastBackup = Backup::latest()->first();
        $autoBackup = SystemSetting::get('auto_backup_enabled', false);

        return view('admin.settings', compact(
            'settings',
            'settingsByGroup',
            'backups',
            'totalBackups',
            'totalSize',
            'lastBackup',
            'autoBackup'
        ));
    }

    /**
     * Simpan semua settings sekaligus dari form blade.
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            // General
            'site_name' => 'sometimes|string|max:255',
            'site_url' => 'sometimes|url|max:255',
            'timezone' => 'sometimes|string|max:50',
            'language' => 'sometimes|in:id,en',
            'contact_email' => 'sometimes|email|max:255',
            'contact_phone' => 'sometimes|nullable|string|max:50',
            'copyright_text' => 'sometimes|nullable|string|max:255',
            'user_registration' => 'sometimes|boolean',

            // Security
            'force_2fa' => 'sometimes|boolean',
            'email_verification' => 'sometimes|boolean',
            'max_login_attempts' => 'sometimes|integer|min:1|max:20',
            'session_timeout' => 'sometimes|integer|min:5|max:1440',
            'password_min_length' => 'sometimes|integer|min:6|max:32',

            // Email / SMTP
            'mail_host' => 'sometimes|nullable|string|max:255',
            'mail_port' => 'sometimes|nullable|integer',
            'mail_username' => 'sometimes|nullable|string|max:255',
            'mail_password' => 'sometimes|nullable|string|max:255',
            'mail_encryption' => 'sometimes|nullable|in:tls,ssl,starttls',
            'mail_from_name' => 'sometimes|nullable|string|max:255',
            'mail_from_address' => 'sometimes|nullable|email|max:255',

            // Payment
            'enable_subscriptions' => 'sometimes|boolean',
            'currency' => 'sometimes|string|size:3',
            'midtrans_server_key' => 'sometimes|nullable|string|max:255',
            'midtrans_client_key' => 'sometimes|nullable|string|max:255',
            'midtrans_sandbox' => 'sometimes|boolean',

            // OAuth / Third-party auth
            'google_client_id' => 'sometimes|nullable|string|max:255',
            'google_client_secret' => 'sometimes|nullable|string|max:255',
            'google_redirect_uri' => 'sometimes|nullable|url|max:255',
            'google_enabled' => 'sometimes|boolean',

            // Social
            'social_facebook' => 'sometimes|nullable|url|max:255',
            'social_twitter' => 'sometimes|nullable|url|max:255',
            'social_instagram' => 'sometimes|nullable|url|max:255',
            'social_linkedin' => 'sometimes|nullable|url|max:255',
            'social_youtube' => 'sometimes|nullable|url|max:255',
            'social_whatsapp' => 'sometimes|nullable|string|max:20',

            // Maintenance
            'maintenance_mode' => 'sometimes|boolean',
            'maintenance_message' => 'sometimes|nullable|string|max:500',
        ]);

        $booleanKeys = [
            'user_registration',
            'force_2fa',
            'email_verification',
            'enable_subscriptions',
            'midtrans_sandbox',
            'google_enabled',
            'maintenance_mode',
        ];

        $integerKeys = [
            'max_login_attempts',
            'session_timeout',
            'password_min_length',
            'mail_port',
        ];

        foreach ($data as $key => $value) {
            $type = 'string';

            if (in_array($key, $booleanKeys, true)) {
                $type = 'boolean';
                $value = $value ? '1' : '0';
            } elseif (in_array($key, $integerKeys, true)) {
                $type = 'integer';
            }

            SystemSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) $value,
                    'type' => $type,
                    'group' => $this->resolveGroup($key),
                ]
            );
        }

        Cache::forget('system_settings_all');
        Cache::forget('maintenance_mode');
        Cache::forget('maintenance_message');

        if (isset($data['maintenance_mode'])) {
            if ($data['maintenance_mode']) {
                Artisan::call('down', [
                    '--message' => $data['maintenance_message'] ?? 'We are under maintenance.',
                    '--retry' => 60,
                ]);
            } else {
                Artisan::call('up');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully',
        ]);
    }

    private function resolveGroup(string $key): string
    {
        if (str_starts_with($key, 'mail_')) {
            return 'email';
        }

        if (str_starts_with($key, 'midtrans_')) {
            return 'payment';
        }

        if (in_array($key, ['enable_subscriptions', 'currency'], true)) {
            return 'payment';
        }

        if (str_starts_with($key, 'social_')) {
            return 'social';
        }

        if (str_starts_with($key, 'google_')) {
            return 'oauth';
        }

        if (str_starts_with($key, 'maintenance')) {
            return 'maintenance';
        }

        if (in_array($key, ['force_2fa', 'email_verification', 'max_login_attempts', 'session_timeout', 'password_min_length'], true)) {
            return 'security';
        }

        return 'general';
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

        Cache::forget('system_settings_all');

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

        Cache::forget('system_settings_all');

        return response()->json([
            'message' => 'Setting updated successfully',
            'setting' => $setting,
        ]);
    }

    public function destroy(SystemSetting $setting): JsonResponse
    {
        Cache::forget('system_settings_all');
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

    public function testEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Mail::raw('This is a test email from ' . config('app.name'), function ($message) use ($validated) {
                $message->to($validated['email'])
                    ->subject('Test Email - ' . config('app.name'));
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent to ' . $validated['email'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function configBackup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auto_backup_enabled' => 'sometimes|boolean',
            'backup_frequency' => 'sometimes|in:daily,weekly,monthly',
            'backup_retention' => 'sometimes|integer|min:1|max:365',
            'auto_backup' => 'sometimes|boolean',
            'keep_days' => 'sometimes|integer|min:1|max:365',
        ]);

        if (array_key_exists('auto_backup', $validated)) {
            $validated['auto_backup_enabled'] = (bool) $validated['auto_backup'];
            unset($validated['auto_backup']);
        }

        if (array_key_exists('keep_days', $validated)) {
            $validated['backup_retention'] = (int) $validated['keep_days'];
            unset($validated['keep_days']);
        }

        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => is_bool($value) ? 'boolean' : 'string', 'group' => 'backup']
            );
        }

        Cache::forget('system_settings_all');

        return response()->json([
            'success' => true,
            'message' => 'Backup configuration saved successfully',
        ]);
    }

    public function backups(Request $request)
    {
        $query = Backup::with('creator')->latest();

        if ($request->filled('type') && in_array($request->string('type')->value(), ['full', 'database', 'files'], true)) {
            $query->where('type', $request->string('type')->value());
        }

        $backups = $query->paginate(20)->withQueryString();
        $totalBackups = Backup::count();
        $totalSize = (int) (Backup::sum('file_size') ?? 0);
        $lastBackup = Backup::latest()->first();
        $autoBackup = SystemSetting::get('auto_backup_enabled', false);

        $dbBackupsSize = (int) Backup::where('type', 'database')->sum('file_size');
        $fileBackupsSize = (int) Backup::where('type', 'files')->sum('file_size');

        $dbBackupsCount = Backup::where('type', 'database')->count();
        $fileBackupsCount = Backup::where('type', 'files')->count();

        $dbUsagePercent = $totalSize > 0 ? round(($dbBackupsSize / $totalSize) * 100) : 0;
        $fileUsagePercent = $totalSize > 0 ? round(($fileBackupsSize / $totalSize) * 100) : 0;

        $backupFrequency = (string) SystemSetting::get('backup_frequency', 'weekly');
        $backupRetention = (int) SystemSetting::get('backup_retention', 30);

        return view('admin.backups', compact(
            'backups',
            'totalBackups',
            'totalSize',
            'lastBackup',
            'autoBackup',
            'dbBackupsSize',
            'fileBackupsSize',
            'dbBackupsCount',
            'fileBackupsCount',
            'dbUsagePercent',
            'fileUsagePercent',
            'backupFrequency',
            'backupRetention'
        ));
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
            'type' => 'sometimes|in:full,database,files',
            'notes' => 'nullable|string',
        ]);

        $type = $validated['type'] ?? 'full';

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '_' . $type . '.zip';
        $path = storage_path('app/backups/' . $filename);

        // Ensure directory exists
        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $backup = Backup::create([
            'file_name' => $filename,
            'file_path' => $path,
            'file_size' => 0,
            'type' => $type,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        try {
            $zip = new ZipArchive();
            $zipStatus = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($zipStatus !== true) {
                throw new \RuntimeException('Failed to create archive file.');
            }

            if (in_array($type, ['database', 'full'], true)) {
                $zip->addFromString('database.sql', $this->generateDatabaseDumpSql());
            }

            if (in_array($type, ['files', 'full'], true)) {
                $this->appendApplicationFilesToZip($zip);
            }

            $zip->close();

            $backup->update([
                'status' => 'completed',
                'completed_at' => now(),
                'file_size' => file_exists($path) ? filesize($path) : 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'backup' => $backup->fresh(),
            ], 201);
        } catch (Throwable $e) {
            $backup->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);

            Log::error('Backup creation failed', [
                'backup_id' => $backup->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Backup gagal dibuat: ' . $e->getMessage(),
            ], 500);
        }
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
            'success' => true,
            'message' => 'Backup deleted successfully',
        ]);
    }

    private function generateDatabaseDumpSql(): string
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver !== 'mysql') {
            throw new \RuntimeException('Backup database saat ini hanya mendukung MySQL/MariaDB.');
        }

        $databaseName = $connection->getDatabaseName() ?: 'database';
        $dump = [];
        $dump[] = '-- SFHUB SQL Backup';
        $dump[] = '-- Generated at: ' . now()->toDateTimeString();
        $dump[] = '-- Database: ' . $databaseName;
        $dump[] = '';
        $dump[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $dump[] = '';

        $rawTables = $connection->select('SHOW TABLES');
        $tables = collect($rawTables)
            ->map(fn($row) => (array) $row)
            ->map(fn($row) => (string) reset($row))
            ->values();

        foreach ($tables as $table) {
            $safeTable = str_replace('`', '``', $table);
            $createTable = $connection->select("SHOW CREATE TABLE `{$safeTable}`");
            $createRow = (array) ($createTable[0] ?? []);
            $createSql = $createRow['Create Table'] ?? (count($createRow) > 1 ? array_values($createRow)[1] : null);

            if (!$createSql) {
                continue;
            }

            $dump[] = "DROP TABLE IF EXISTS `{$safeTable}`;";
            $dump[] = $createSql . ';';

            $rows = $connection->table($table)->get();
            foreach ($rows as $row) {
                $data = (array) $row;
                $columns = implode(', ', array_map(fn($column) => '`' . str_replace('`', '``', (string) $column) . '`', array_keys($data)));
                $values = implode(', ', array_map(fn($value) => $this->convertValueToSql($value), array_values($data)));
                $dump[] = "INSERT INTO `{$safeTable}` ({$columns}) VALUES ({$values});";
            }

            $dump[] = '';
        }

        $dump[] = 'SET FOREIGN_KEY_CHECKS=1;';

        return implode("\n", $dump) . "\n";
    }

    private function convertValueToSql(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return DB::connection()->getPdo()->quote($value->format('Y-m-d H:i:s'));
        }

        return DB::connection()->getPdo()->quote((string) $value);
    }

    private function appendApplicationFilesToZip(ZipArchive $zip): void
    {
        $sources = [
            base_path('app') => 'app',
            base_path('config') => 'config',
            base_path('resources/views') => 'resources/views',
            base_path('routes') => 'routes',
            storage_path('app/public') => 'storage/app/public',
            public_path() => 'public',
        ];

        foreach ($sources as $sourcePath => $zipPath) {
            $this->addPathToZip($zip, $sourcePath, $zipPath);
        }

        $envExample = base_path('.env.example');
        if (File::exists($envExample)) {
            $zip->addFile($envExample, '.env.example');
        }
    }

    private function addPathToZip(ZipArchive $zip, string $path, string $zipPath): void
    {
        if (!File::exists($path)) {
            return;
        }

        if (File::isFile($path)) {
            $zip->addFile($path, ltrim(str_replace('\\', '/', $zipPath), '/'));
            return;
        }

        $files = File::allFiles($path);

        foreach ($files as $file) {
            $realPath = $file->getRealPath();

            if (!$realPath) {
                continue;
            }

            if (str_contains(str_replace('\\', '/', $realPath), 'storage/app/backups/')) {
                continue;
            }

            $relative = ltrim(str_replace('\\', '/', $file->getRelativePathname()), '/');
            $entryPath = trim($zipPath, '/') . '/' . $relative;

            $zip->addFile($realPath, $entryPath);
        }
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
}
