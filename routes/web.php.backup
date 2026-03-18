<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CreativeStudioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\FocusController;
use App\Http\Controllers\GeneralTrackerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\PklController;
use App\Http\Controllers\ProductivityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SmartCalendarController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;


// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected Routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::get('/donation', [DonationController::class, 'show'])->name('donation.show');
    Route::post('/donation', [DonationController::class, 'process'])->name('donation.process');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile
    // ── PROFILE & SETTINGS ────────────────────────────────────────────────
    Route::get('/profile',             [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',             [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',    [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');

    Route::delete('/profile',          [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── NOTIFICATIONS ─────────────────────────────────────────────────────
    Route::get('/notifications',              [ProfileController::class, 'getNotifications'])->name('notifications.index');
    Route::post('/notifications/read-all',    [ProfileController::class, 'markAllRead'])->name('notifications.read-all');
    Route::delete('/notifications',           [ProfileController::class, 'clearAllNotifications'])->name('notifications.clear');
    Route::post('/notifications/{id}/read',   [ProfileController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{id}',      [ProfileController::class, 'destroyNotification'])->name('notifications.destroy');


    // Dashboard utama
    Route::get('/dashboard', [FocusController::class, 'index'])->name('dashboard');

    // ════════════════════════════════════════════════════════════════════════
    // GENERAL TRACKER - UPDATED dengan route yang lebih bersih
    // ══════════════════════════════════
    Route::post('/tasks',             [GeneralTrackerController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/quick-add',   [GeneralTrackerController::class, 'quickAdd'])->name('tasks.quick-add');
    // STATUS: harus pakai POST + body JSON {status: 'done'|'todo'|'doing'}
    Route::post('/tasks/{id}/status', [GeneralTrackerController::class, 'updateStatus'])->name('tasks.update-status');
    // DELETE: menggunakan method DELETE atau POST + _method=DELETE
    Route::delete('/tasks/{id}',      [GeneralTrackerController::class, 'destroy'])->name('tasks.destroy');

    Route::prefix('tasks')->group(function () {
        Route::post('/{id}/status', [GeneralTrackerController::class, 'updateStatus'])->name('tasks.status.update');
        Route::post('/{id}/complete', [GeneralTrackerController::class, 'completeTask'])->name('tasks.complete');
    });

    // Creative Studio Routes
    Route::prefix('dashboard/creative')->middleware('verified')->group(function () {
        Route::get('/', [CreativeStudioController::class, 'index'])->name('dashboard.creative');
        Route::post('/', [CreativeStudioController::class, 'store'])->name('dashboard.creative.store');
        Route::get('/task/{id}', [CreativeStudioController::class, 'showTaskDetail'])->name('dashboard.creative.task.show');
        Route::post('/task/{taskId}/subtask', [CreativeStudioController::class, 'storeSubtask'])->name('dashboard.creative.subtask.store');
        Route::put('/task/{taskId}/subtask/{subtaskId}', [CreativeStudioController::class, 'updateSubtask'])->name('dashboard.creative.subtask.update');
        Route::delete('/task/{taskId}/subtask/{subtaskId}', [CreativeStudioController::class, 'destroySubtask'])->name('dashboard.creative.subtask.destroy');
        Route::post('/task/{taskId}/create-default-subtasks', [CreativeStudioController::class, 'createDefaultSubtasks']);
        Route::post('/{id}/status', [CreativeStudioController::class, 'updateStatus'])->name('dashboard.creative.status.update');
        Route::post('/{id}/links', [CreativeStudioController::class, 'addLink'])->name('dashboard.creative.links.add');

        // TAMBAHKAN ROUTE INI DI SINI:
        Route::post('/{id}/done', [CreativeStudioController::class, 'markDone'])->name('dashboard.creative.mark-done');
        Route::post('/{id}/status', [CreativeStudioController::class, 'updateStatus'])->name('dashboard.creative.status.update');
        Route::post('/{id}/links', [CreativeStudioController::class, 'addLink'])->name('dashboard.creative.links.add');
    });

    Route::prefix('dashboard/creative')->name('dashboard.creative.')->group(function () {
        Route::get('/', [CreativeStudioController::class, 'index'])->name('index');
        Route::post('/', [CreativeStudioController::class, 'store'])->name('store');
        Route::post('/{task}/status', [CreativeStudioController::class, 'updateStatus'])->name('update-status');
        Route::post('/{task}/links', [CreativeStudioController::class, 'addLink'])->name('add-link');
        Route::put('/{task}', [CreativeStudioController::class, 'update'])->name('update');
        Route::delete('/{task}', [CreativeStudioController::class, 'destroy'])->name('destroy');
    });

    // ════════════════════════════════════════════════════════════════════════
    // SMART CALENDAR - UPDATED dengan route updateSchedule
    // ════════════════════════════════════════════════════════════════════════
    Route::get('/dashboard/smart-calendar', [SmartCalendarController::class, 'index'])->name('dashboard.smartcalendar');
    Route::get('/calendar/day/{date}',      [SmartCalendarController::class, 'showDay'])->name('calendar.day');

    // Events (one-off)
    Route::post('/calendar/events',          [SmartCalendarController::class, 'storeEvent'])->name('calendar.events.store');
    Route::delete('/calendar/events/{id}',   [SmartCalendarController::class, 'destroyEvent'])->name('calendar.events.destroy');

    // Schedules (recurring) — UPDATE endpoint ditambahkan
    Route::post('/calendar/schedules',       [SmartCalendarController::class, 'storeSchedule'])->name('calendar.schedules.store');
    Route::put('/calendar/schedules/{id}',   [SmartCalendarController::class, 'updateSchedule'])->name('calendar.schedules.update');
    Route::delete('/calendar/schedules/{id}', [SmartCalendarController::class, 'destroySchedule'])->name('calendar.schedules.destroy');

    // Tasks
    Route::post('/calendar/tasks',           [SmartCalendarController::class, 'storeTask'])->name('calendar.tasks.store');
    Route::post('/tasks/{id}/complete',      [SmartCalendarController::class, 'markTaskComplete'])->name('tasks.complete');

    // Productivity
    Route::get('/dashboard/productivity', [ProductivityController::class, 'index'])->name('dashboard.productivity');
    Route::post('/productivity/log', [ProductivityController::class, 'store'])->name('productivity.store');

    // ── ACADEMIC ──────────────────────────────────────────────────────────
    Route::prefix('academic')->name('academic.')->group(function () {
        // Courses (Subjects)
        Route::post('/courses',         [AcademicController::class, 'storeCourse'])->name('courses.store');
        Route::put('/courses/{id}',    [AcademicController::class, 'updateCourse'])->name('courses.update');
        Route::delete('/courses/{id}', [AcademicController::class, 'destroyCourse'])->name('courses.destroy');

        // Academic Tasks
        Route::post('/tasks',               [AcademicController::class, 'storeTask'])->name('tasks.store');
        Route::put('/tasks/{id}',          [AcademicController::class, 'updateTask'])->name('tasks.update');
        Route::post('/tasks/{id}/status',  [AcademicController::class, 'updateTaskStatus'])->name('tasks.status');
        Route::delete('/tasks/{id}',       [AcademicController::class, 'destroyTask'])->name('tasks.destroy');

        // Thesis Milestones
        Route::post('/milestones',         [AcademicController::class, 'storeMilestone'])->name('milestones.store');
        Route::put('/milestones/{id}',    [AcademicController::class, 'updateMilestone'])->name('milestones.update');
        Route::delete('/milestones/{id}', [AcademicController::class, 'destroyMilestone'])->name('milestones.destroy');
        Route::post('/milestones/{id}/toggle', [AcademicController::class, 'toggleMilestone'])->name('milestones.toggle');

        Route::post('/sessions/{id}/holiday', [AcademicController::class, 'markHolidaySession'])->name('sessions.holiday');
        Route::post('/sessions/{id}/complete', [AcademicController::class, 'completeSession'])->name('sessions.complete');

        // UBAH ROUTE INI:
        Route::post('/sessions/{id}/reschedule', [AcademicController::class, 'updateSessionSchedule'])->name('sessions.reschedule');
    });

    // ════════════════════════════════════════════════════════════════════════
    // PKL MANAGER - UPDATED dengan struktur baru
    // ════════════════════════════════════════════════════════════════════════
    Route::get('/dashboard/pkl', [PklController::class, 'index'])->name('dashboard.pkl');

    Route::prefix('dashboard/pkl')->name('pkl.')->group(function () {
        // Info PKL — POST (upsert: create jika belum ada, update jika sudah ada)
        Route::post('/info',          [PklController::class, 'storePklInfo'])->name('info.store');
        Route::put('/info/{id}',      [PklController::class, 'updatePklInfo'])->name('info.update');

        // Jadwal PKL (termasuk split-shift)
        Route::post('/schedule',      [PklController::class, 'updateSchedule'])->name('schedule.update');

        // Log Aktivitas
        Route::post('/activity',      [PklController::class, 'storeActivity'])->name('activity.store');
        Route::put('/activity/{id}',  [PklController::class, 'updateActivity'])->name('activity.update');
        Route::delete('/activity/{id}', [PklController::class, 'destroyActivity'])->name('activity.destroy');
    });

    // Dashboard views (statis)

    Route::get('/dashboard/focus', [FocusController::class, 'index'])->name('dashboard.focus');
    Route::post('/dashboard/focus/task', [FocusController::class, 'storeTask'])->name('focus.task.store');
    Route::get('/dashboard/academic', [AcademicController::class, 'index'])->name('dashboard.academic');

    // Route dashboard/pkl sudah didefinisikan di atas, jangan duplikasi
    // Route::get('/dashboard/pkl', [DashboardController::class, 'pkl'])->name('dashboard.pkl'); // COMMENT atau HAPUS

    // API untuk AJAX
    Route::get('/dashboard/recommendations', [DashboardController::class, 'getRecommendations'])->name('dashboard.recommendations');
    Route::get('/dashboard/today-tasks', [DashboardController::class, 'getTodayTasks'])->name('dashboard.today-tasks');
    Route::get('/dashboard/today-schedule', [DashboardController::class, 'getTodayScheduleApi'])->name('dashboard.today-schedule');

    // ══════════════════════════════════════════════════════════════════
    // FINANCE
    // ══════════════════════════════════════════════════════════════════
    Route::get('/dashboard/finance', [FinanceController::class, 'index'])->name('dashboard.finance');
    Route::get('/finance/summary',   [FinanceController::class, 'getSummary'])->name('finance.summary');

    Route::post('/finance/accounts',                    [FinanceController::class, 'storeAccount'])->name('finance.accounts.store');
    Route::put('/finance/accounts/{account}',          [FinanceController::class, 'updateAccount'])->name('finance.accounts.update');
    Route::patch('/finance/accounts/{account}/balance',  [FinanceController::class, 'updateAccountBalance'])->name('finance.accounts.balance');
    Route::delete('/finance/accounts/{account}',          [FinanceController::class, 'destroyAccount'])->name('finance.accounts.destroy');

    Route::get('/finance/transactions',                [FinanceController::class, 'getTransactions'])->name('finance.transactions.index');
    Route::post('/finance/transactions',                [FinanceController::class, 'storeTransaction'])->name('finance.transactions.store');
    Route::delete('/finance/transactions/{transaction}',  [FinanceController::class, 'destroyTransaction'])->name('finance.transactions.destroy');

    Route::post('/finance/transfer', [FinanceController::class, 'storeTransfer'])->name('finance.transfer.store');

    Route::post('/finance/savings-goals',               [FinanceController::class, 'storeSavingsGoal'])->name('finance.savings.store');
    Route::put('/finance/savings-goals/{savingsGoal}', [FinanceController::class, 'updateSavingsGoal'])->name('finance.savings.update');
    Route::delete('/finance/savings-goals/{savingsGoal}', [FinanceController::class, 'destroySavingsGoal'])->name('finance.savings.destroy');

    Route::post('/finance/budgets',           [FinanceController::class, 'storeBudget'])->name('finance.budgets.store');
    Route::put('/finance/budgets/{budget}',  [FinanceController::class, 'updateBudget'])->name('finance.budgets.update');
    Route::delete('/finance/budgets/{budget}',  [FinanceController::class, 'destroyBudget'])->name('finance.budgets.destroy');

    Route::post('/finance/pending-needs',                         [FinanceController::class, 'storePendingNeed'])->name('finance.needs.store');
    Route::post('/finance/pending-needs/{pendingNeed}/purchase',  [FinanceController::class, 'purchasePendingNeed'])->name('finance.needs.purchase');
    Route::post('/finance/pending-needs/{pendingNeed}/cancel',    [FinanceController::class, 'cancelPendingNeed'])->name('finance.needs.cancel');
    Route::delete('/finance/pending-needs/{pendingNeed}',           [FinanceController::class, 'destroyPendingNeed'])->name('finance.needs.destroy');

    // ══════════════════════════════════════════════════════════════════
    // ASSETS
    // ⚠️ Urutan KRITIS: statis (/accounts, /summary) HARUS di atas /{id}
    // ══════════════════════════════════════════════════════════════════
    Route::get('/dashboard/assets', [AssetController::class, 'index'])->name('dashboard.assets');
    Route::get('/assets/summary',   [AssetController::class, 'getSummary'])->name('assets.summary');

    Route::post('/assets/accounts',                 [AssetController::class, 'storeAccount'])->name('assets.accounts.store');
    Route::put('/assets/accounts/{id}',            [AssetController::class, 'updateAccount'])->name('assets.accounts.update');
    Route::patch('/assets/accounts/{id}/balance',    [AssetController::class, 'updateAccountBalance'])->name('assets.accounts.balance');
    Route::delete('/assets/accounts/{id}',            [AssetController::class, 'destroyAccount'])->name('assets.accounts.destroy');

    Route::post('/assets',            [AssetController::class, 'store'])->name('assets.store');
    Route::get('/assets/{id}',       [AssetController::class, 'show'])->name('assets.show');
    Route::put('/assets/{id}',       [AssetController::class, 'update'])->name('assets.update');
    Route::patch('/assets/{id}/value', [AssetController::class, 'updateValue'])->name('assets.value');
    Route::delete('/assets/{id}',       [AssetController::class, 'destroy'])->name('assets.destroy');

    // ══════════════════════════════════════════════════════════════════
    // DEBTS
    // ⚠️ /payments/{id} HARUS di atas /{id}
    // ══════════════════════════════════════════════════════════════════
    Route::get('/dashboard/debts', [DebtController::class, 'index'])->name('dashboard.debts');

    Route::delete('/debts/payments/{id}',   [DebtController::class, 'destroyPayment'])->name('debts.payments.destroy');

    Route::post('/debts',         [DebtController::class, 'store'])->name('debts.store');
    Route::put('/debts/{id}',    [DebtController::class, 'update'])->name('debts.update');
    Route::delete('/debts/{id}',    [DebtController::class, 'destroy'])->name('debts.destroy');

    Route::post('/debts/{id}/payments',  [DebtController::class, 'addPayment'])->name('debts.payments.store');
    Route::post('/debts/{id}/mark-paid', [DebtController::class, 'markAsPaid'])->name('debts.mark-paid');
    Route::get('/debts/{id}/payments',  [DebtController::class, 'getPayments'])->name('debts.payments.index');

    // ══════════════════════════════════════════════════════════════════
    // INVESTMENTS
    // ⚠️ /summary dan /purchases/{id} HARUS di atas /{id}
    // ══════════════════════════════════════════════════════════════════
    Route::get('/dashboard/investments', [InvestmentController::class, 'index'])->name('dashboard.investments');

    Route::get('/investments/summary',           [InvestmentController::class, 'getSummary'])->name('investments.summary');
    Route::delete('/investments/purchases/{id}',    [InvestmentController::class, 'destroyPurchase'])->name('investments.purchases.destroy');

    // ── INVESTMENT INSTRUMENTS ─────────────────────────────────────────
    // Route untuk input manual (HARUS di atas route /investments/{id})
    Route::post('/investments/manual', [InvestmentController::class, 'storeInstrumentWithInitial'])->name('investments.instruments.manual');

    Route::post('/investments',                    [InvestmentController::class, 'storeInstrument'])->name('investments.instruments.store');
    Route::get('/investments/{id}',               [InvestmentController::class, 'showInstrument'])->name('investments.instruments.show');
    Route::put('/investments/{id}',               [InvestmentController::class, 'updateInstrument'])->name('investments.instruments.update');
    Route::patch('/investments/{id}/price',        [InvestmentController::class, 'updatePrice'])->name('investments.instruments.price');
    Route::patch('/investments/{id}/position',     [InvestmentController::class, 'updatePosition'])->name('investments.instruments.position');

    // Route untuk update manual value (PATCH, spesifik)
    Route::patch('/investments/{id}/manual-value', [InvestmentController::class, 'updateManualValue'])->name('investments.instruments.manual-value');

    Route::delete('/investments/{id}',             [InvestmentController::class, 'destroyInstrument'])->name('investments.instruments.destroy');

    // ── INVESTMENT PURCHASES ───────────────────────────────────────────
    Route::post('/investments/{id}/purchases', [InvestmentController::class, 'storePurchase'])->name('investments.purchases.store');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                             [AdminController::class, 'index'])->name('index');

    // User Management
    Route::get('/users',                        [AdminController::class, 'users'])->name('users');
    Route::get('/users/create',                 [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users',                       [AdminController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{user}/toggle-active',  [AdminController::class, 'toggleUserActive'])->name('users.toggle');
    Route::delete('/users/{user}',              [AdminController::class, 'destroyUser'])->name('users.destroy');

    // Landing Content Management
    Route::get('/landing',                          [AdminController::class, 'landingContent'])->name('landing');
    Route::post('/landing',                         [AdminController::class, 'storeLandingContent'])->name('landing.store');
    Route::patch('/landing/{content}',              [AdminController::class, 'updateLandingContent'])->name('landing.update');
    Route::delete('/landing/{content}',             [AdminController::class, 'destroyLandingContent'])->name('landing.destroy');
});
