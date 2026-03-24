<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key')->toArray();

        // Juga kelompokkan per group untuk tampilan
        $settingsByGroup = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        // Backup stats
        $backups      = Backup::latest()->paginate(10);
        $totalBackups = Backup::count();
        $totalSize    = Backup::sum('file_size') ?? 0;
        $lastBackup   = Backup::latest()->first();
        $autoBackup   = SystemSetting::get('auto_backup_enabled', false);

        return view('admin.settings', compact(
            'settings', 'settingsByGroup',
            'backups', 'totalBackups', 'totalSize', 'lastBackup', 'autoBackup'
        ));
    }

    /**
     * Simpan semua settings sekaligus dari form blade.
     * Ini yang dipanggil saveSettings() di JS.
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            // General
            'site_name'         => 'sometimes|string|max:255',
            'site_url'          => 'sometimes|url|max:255',
            'timezone'          => 'sometimes|string|max:50',
            'language'          => 'sometimes|in:id,en',
            'contact_email'     => 'sometimes|email|max:255',
            'contact_phone'     => 'sometimes|nullable|string|max:50',
            'copyright_text'    => 'sometimes|nullable|string|max:255',
            'user_registration' => 'sometimes|boolean',

            // Security
            'force_2fa'           => 'sometimes|boolean',
            'email_verification'  => 'sometimes|boolean',
            'max_login_attempts'  => 'sometimes|integer|min:1|max:20',
            'session_timeout'     => 'sometimes|integer|min:5|max:1440',
            'password_min_length' => 'sometimes|integer|min:6|max:32',

            // Email / SMTP
            'mail_host'       => 'sometimes|nullable|string|max:255',
            'mail_port'       => 'sometimes|nullable|integer',
            'mail_username'   => 'sometimes|nullable|string|max:255',
            'mail_password'   => 'sometimes|nullable|string|max:255',
            'mail_encryption' => 'sometimes|nullable|in:tls,ssl,starttls',

            // Payment
            'enable_subscriptions'  => 'sometimes|boolean',
            'currency'              => 'sometimes|string|size:3',
            'midtrans_server_key'   => 'sometimes|nullable|string|max:255',
            'midtrans_client_key'   => 'sometimes|nullable|string|max:255',
            'midtrans_sandbox'      => 'sometimes|boolean',

            // Social
            'social_facebook'  => 'sometimes|nullable|url|max:255',
            'social_twitter'   => 'sometimes|nullable|url|max:255',
            'social_instagram' => 'sometimes|nullable|url|max:255',
            'social_linkedin'  => 'sometimes|nullable|url|max:255',
            'social_youtube'   => 'sometimes|nullable|url|max:255',
            'social_whatsapp'  => 'sometimes|nullable|string|max:20',

            // Maintenance
            'maintenance_mode'    => 'sometimes|boolean',
            'maintenance_message' => 'sometimes|nullable|string|max:500',
        ]);

        // Tentukan type per key
        $booleanKeys = [
            'user_registration', 'force_2fa', 'email_verification',
            'enable_subscriptions', 'midtrans_sandbox', 'maintenance_mode',
        ];
        $integerKeys = [
            'max_login_attempts', 'session_timeout', 'password_min_length', 'mail_port',
        ];

        foreach ($data as $key => $value) {
            $type = 'string';
            if (in_array($key, $booleanKeys)) {
                $type  = 'boolean';
                $value = $value ? '1' : '0';
            } elseif (in_array($key, $integerKeys)) {
                $type = 'integer';
            }

            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value, 'type' => $type, 'group' => $this->resolveGroup($key)]
            );
        }

        // Flush cache agar AppServiceProvider membaca nilai baru
        Cache::forget('system_settings_all');

        // Jika maintenance mode diubah, panggil artisan
        if (isset($data['maintenance_mode'])) {
            if ($data['maintenance_mode']) {
                Artisan::call('down', [
                    '--message' => $data['maintenance_message'] ?? 'We are under maintenance.',
                    '--retry'   => 60,
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

    /**
     * Tentukan group berdasarkan key name.
     */
    private function resolveGroup(string $key): string
    {
        if (str_starts_with($key, 'mail_'))       return 'email';
        if (str_starts_with($key, 'midtrans_'))   return 'payment';
        if (str_starts_with($key, 'social_'))     return 'social';
        if (str_starts_with($key, 'maintenance')) return 'maintenance';
        if (in_array($key, ['force_2fa', 'email_verification', 'max_login_attempts', 'session_timeout', 'password_min_length'])) {
            return 'security';
        }
        return 'general';
    }

    // ──────────────────────────────────────────
    // CRUD individual (API)
    // ──────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key'         => 'required|string|max:255|unique:system_settings,key',
            'value'       => 'nullable',
            'type'        => 'required|in:string,integer,boolean,json',
            'group'       => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_public'   => 'boolean',
        ]);

        $setting = SystemSetting::create($validated);
        Cache::forget('system_settings_all');

        return response()->json(['message' => 'Setting created successfully', 'setting' => $setting], 201);
    }

    public function update(Request $request, SystemSetting $setting): JsonResponse
    {
        $validated = $request->validate([
            'value'       => 'nullable',
            'type'        => 'sometimes|in:string,integer,boolean,json',
            'group'       => 'sometimes|string|max:50',
            'description' => 'nullable|string',
            'is_public'   => 'sometimes|boolean',
        ]);

        $setting->update($validated);
        Cache::forget('system_settings_all');

        return response()->json(['message' => 'Setting updated successfully', 'setting' => $setting]);
    }

    public function destroy(SystemSetting $setting): JsonResponse
    {
        Cache::forget('system_settings_all');
        $setting->delete();

        return response()->json(['message' => 'Setting deleted successfully']);
    }

    public function getByGroup(string $group): JsonResponse
    {
        return response()->json(SystemSetting::where('group', $group)->get());
    }

    public function getValue(string $key): JsonResponse
    {
        return response()->json(['key' => $key, 'value' => SystemSetting::get($key)]);
    }

    public function setValue(Request $request, string $key): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required',
            'type'  => 'required|in:string,integer,boolean,json',
            'group' => 'nullable|string|max:50',
        ]);

        $setting = SystemSetting::set($key, $validated['value'], $validated['type'], $validated['group'] ?? 'general');
        Cache::forget('system_settings_all');

        return response()->json(['message' => 'Setting saved successfully', 'setting' => $setting]);
    }

    // ──────────────────────────────────────────
    // Utilities
    // ──────────────────────────────────────────

    public function clearCache(): JsonResponse
    {
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return response()->json(['message' => 'All caches cleared successfully']);
    }

    public function getSystemInfo(): JsonResponse
    {
        return response()->json([
            'php_version'         => PHP_VERSION,
            'laravel_version'     => app()->version(),
            'environment'         => app()->environment(),
            'debug_mode'          => config('app.debug'),
            'maintenance_mode'    => app()->isDownForMaintenance(),
            'timezone'            => config('app.timezone'),
            'locale'              => config('app.locale'),
            'database_connection' => config('database.default'),
            'cache_driver'        => config('cache.default'),
            'session_driver'      => config('session.driver'),
            'disk_space'          => [
                'free'  => disk_free_space(storage_path()),
                'total' => disk_total_space(storage_path()),
            ],
        ]);
    }

    // ──────────────────────────────────────────
    // Email test — BENAR-BENAR kirim email
    // ──────────────────────────────────────────

    public function testEmail(Request $request): JsonResponse
    {
        $validated = $request->validate(['email' => 'required|email']);

        try {
            Mail::raw('This is a test email from ' . config('app.name'), function ($message) use ($validated) {
                $message->to($validated['email'])
                    ->subject('Test Email — ' . config('app.name'));
            });

            return response()->json(['success' => true, 'message' => 'Test email sent to ' . $validated['email']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // Backup
    // ──────────────────────────────────────────

    public function backups()
    {
        $backups      = Backup::with('creator')->latest()->paginate(20);
        $totalBackups = Backup::count();
        $totalSize    = Backup::sum('file_size');
        $lastBackup   = Backup::latest()->first();
        $autoBackup   = SystemSetting::get('auto_backup_enabled', false);

        return view('admin.backups', compact('backups', 'totalBackups', 'totalSize', 'lastBackup', 'autoBackup'));
    }

    public function getBackups(): JsonResponse
    {
        $backups = Backup::with('creator')->latest()->paginate(20);

        return response()->json([
            'data' => $backups->map(fn ($b) => [
                'id'          => $b->id,
                'file_name'   => $b->file_name,
                'file_size'   => $this->formatBytes($b->file_size),
                'type'        => $b->type,
                'status'      => $b->status,
                'created_by'  => $b->creator?->name ?? 'System',
                'completed_at'=> $b->completed_at?->format('Y-m-d H:i:s'),
                'created_at'  => $b->created_at->format('Y-m-d H:i:s'),
            ]),
            'pagination' => [
                'current_page' => $backups->currentPage(),
                'last_page'    => $backups->lastPage(),
                'per_page'     => $backups->perPage(),
                'total'        => $backups->total(),
            ],
        ]);
    }

    public function createBackup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'  => 'required|in:full,database,files',
            'notes' => 'nullable|string',
        ]);

        $filename  = 'backup_' . date('Y-m-d_H-i-s') . '_' . $validated['type'] . '.zip';
        $backupDir = storage_path('app/backups');
        $path      = $backupDir . '/' . $filename;

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backup = Backup::create([
            'file_name'  => $filename,
            'file_path'  => $path,
            'file_size'  => 0,
            'type'       => $validated['type'],
            'status'     => 'pending',
            'notes'      => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        try {
            // Backup database
            if (in_array($validated['type'], ['full', 'database'])) {
                $dbName = config('database.connections.' . config('database.default') . '.database');
                $dbUser = config('database.connections.' . config('database.default') . '.username');
                $dbPass = config('database.connections.' . config('database.default') . '.password');
                $sqlFile = $backupDir . '/db_' . date('Y-m-d_H-i-s') . '.sql';

                // Jalankan mysqldump (pastikan mysqldump tersedia di server)
                $cmd = sprintf(
                    'MYSQL_PWD=%s mysqldump -u %s %s > %s 2>&1',
                    escapeshellarg($dbPass),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbName),
                    escapeshellarg($sqlFile)
                );
                exec($cmd, $output, $returnCode);
            }

            // Buat ZIP
            $zip = new \ZipArchive();
            if ($zip->open($path, \ZipArchive::CREATE) === true) {
                if (isset($sqlFile) && file_exists($sqlFile)) {
                    $zip->addFile($sqlFile, 'database.sql');
                }
                if (in_array($validated['type'], ['full', 'files'])) {
                    // Tambahkan folder storage/app/public (media uploads)
                    $this->addFolderToZip($zip, storage_path('app/public'), 'storage/');
                }
                $zip->close();

                // Hapus file SQL sementara
                if (isset($sqlFile) && file_exists($sqlFile)) {
                    unlink($sqlFile);
                }
            }

            $backup->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'file_size'    => file_exists($path) ? filesize($path) : 0,
            ]);
        } catch (\Exception $e) {
            $backup->update(['status' => 'failed']);
            return response()->json(['message' => 'Backup failed: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Backup created successfully', 'backup' => $backup], 201);
    }

    private function addFolderToZip(\ZipArchive $zip, string $folder, string $prefix): void
    {
        if (!is_dir($folder)) return;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $relativePath = $prefix . substr($file->getRealPath(), strlen($folder) + 1);
                $zip->addFile($file->getRealPath(), $relativePath);
            }
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

        return response()->json(['message' => 'Backup deleted successfully']);
    }

    public function configBackup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auto_backup_enabled' => 'boolean',
            'backup_frequency'    => 'in:daily,weekly,monthly',
            'backup_retention'    => 'integer|min:1|max:30',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => is_bool($value) ? 'boolean' : 'string', 'group' => 'backup']
            );
        }

        Cache::forget('system_settings_all');

        return response()->json(['message' => 'Backup configuration saved successfully']);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }
}
