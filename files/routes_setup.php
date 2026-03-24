<?php

// ============================================================
// FILE: routes/web.php  &  routes/api.php
// Tambahkan route-route ini
// ============================================================

// ── Di routes/web.php ──────────────────────────────────────

// Midtrans callback (WAJIB dikecualikan dari CSRF)
// Tambahkan 'midtrans/*' ke $except di app/Http/Middleware/VerifyCsrfToken.php
Route::prefix('midtrans')->group(function () {
    Route::post('/webhook', [App\Http\Controllers\MidtransController::class, 'webhook'])
        ->name('midtrans.webhook');
    Route::get('/finish', [App\Http\Controllers\MidtransController::class, 'finish'])
        ->name('midtrans.finish');
});

// ── Di routes/api.php atau routes/web.php (admin) ──────────

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Settings
    Route::get('/settings',             [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings');
    Route::post('/settings/save',       [App\Http\Controllers\Admin\SettingController::class, 'saveSettings'])->name('admin.settings.save');
    Route::post('/settings/clear-cache',[App\Http\Controllers\Admin\SettingController::class, 'clearCache'])->name('admin.settings.clear-cache');
    Route::post('/settings/test-email', [App\Http\Controllers\Admin\SettingController::class, 'testEmail'])->name('admin.settings.test-email');
    Route::get('/settings/system-info', [App\Http\Controllers\Admin\SettingController::class, 'getSystemInfo'])->name('admin.settings.system-info');

    // Backup
    Route::get('/backups',              [App\Http\Controllers\Admin\SettingController::class, 'backups'])->name('admin.backups');
    Route::get('/backups/list',         [App\Http\Controllers\Admin\SettingController::class, 'getBackups'])->name('admin.backups.list');
    Route::post('/backups',             [App\Http\Controllers\Admin\SettingController::class, 'createBackup'])->name('admin.backups.create');
    Route::get('/backups/{backup}/download', [App\Http\Controllers\Admin\SettingController::class, 'downloadBackup'])->name('admin.backups.download');
    Route::delete('/backups/{backup}',  [App\Http\Controllers\Admin\SettingController::class, 'destroyBackup'])->name('admin.backups.destroy');
    Route::post('/backups/config',      [App\Http\Controllers\Admin\SettingController::class, 'configBackup'])->name('admin.backups.config');

    // Subscription
    Route::get('/subscriptions',        [App\Http\Controllers\Admin\SubscriptionController::class, 'getSubscriptions'])->name('admin.subscriptions');
    Route::get('/subscription-plans',   [App\Http\Controllers\Admin\SubscriptionController::class, 'getPlans'])->name('admin.plans');
    Route::post('/subscription-plans',  [App\Http\Controllers\Admin\SubscriptionController::class, 'storePlan']);
    Route::put('/subscription-plans/{plan}',    [App\Http\Controllers\Admin\SubscriptionController::class, 'updatePlan']);
    Route::delete('/subscription-plans/{plan}', [App\Http\Controllers\Admin\SubscriptionController::class, 'destroyPlan']);
    Route::post('/subscriptions/{subscription}/cancel', [App\Http\Controllers\Admin\SubscriptionController::class, 'cancelSubscription']);
    Route::post('/subscriptions/{subscription}/extend', [App\Http\Controllers\Admin\SubscriptionController::class, 'extendSubscription']);
});

// Subscription untuk user biasa (beli plan)
Route::middleware(['auth'])->group(function () {
    Route::post('/subscribe', [App\Http\Controllers\MidtransController::class, 'createTransaction'])->name('subscribe');
});


// ============================================================
// FILE: app/Http/Middleware/VerifyCsrfToken.php
// Tambahkan midtrans/webhook ke $except
// ============================================================
/*
protected $except = [
    'midtrans/webhook',
    'midtrans/*',
];
*/


// ============================================================
// FILE: bootstrap/app.php (Laravel 11) ATAU Kernel.php (L10)
// Daftarkan middleware CheckMaintenanceMode
// ============================================================

// Laravel 11 — di bootstrap/app.php:
/*
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\CheckMaintenanceMode::class);
})
*/

// Laravel 10 — di app/Http/Kernel.php, tambahkan ke $middlewareGroups['web']:
/*
protected $middlewareGroups = [
    'web' => [
        ...
        \App\Http\Middleware\CheckMaintenanceMode::class,
    ],
];
*/
