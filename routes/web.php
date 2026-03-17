<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SmartCalendarController;
use App\Http\Controllers\CreativeStudioController;
use App\Http\Controllers\GeneralTrackerController;
use App\Http\Controllers\ProductivityLogController;
// ── Controllers baru ──────────────────────────────────────────────────
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\InvestmentController;

use App\Livewire\Dashboard\TodayPriority;
use App\Livewire\Dashboard\ProductivityDashboard;
use App\Livewire\Dashboard\FinanceDashboard;
use App\Livewire\Dashboard\AssetsDashboard;
use App\Livewire\Dashboard\DebtsDashboard;
use App\Livewire\Dashboard\InvestmentsDashboard;
use App\Livewire\Schedule\ScheduleManager;
use App\Livewire\Tasks\TaskManager;
use App\Livewire\Settings\UserSettings;

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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Dashboard utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tracker', [GeneralTrackerController::class, 'index'])->name('dashboard.tracker');

    // Task Routes
    Route::post('/tasks', [GeneralTrackerController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/quick-add', [GeneralTrackerController::class, 'quickAdd'])->name('tasks.quick-add');
    Route::post('/tasks/{id}/status', [GeneralTrackerController::class, 'updateStatus'])->name('tasks.update-status');
    Route::delete('/tasks/{id}', [GeneralTrackerController::class, 'destroy'])->name('tasks.destroy');

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
        Route::post('/task/{taskId}/create-default-subtasks', [CreativeStudioController::class, 'createDefaultSubtasks']);
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

    // Smart Calendar Routes
    Route::get('/dashboard/smart-calendar', [SmartCalendarController::class, 'index'])->name('dashboard.smartcalendar');
    Route::get('/calendar/day/{date}', [SmartCalendarController::class, 'showDay'])->name('calendar.day');
    Route::post('/calendar/events', [SmartCalendarController::class, 'storeEvent'])->name('calendar.events.store');
    Route::post('/calendar/schedules', [SmartCalendarController::class, 'storeSchedule'])->name('calendar.schedules.store');
    Route::delete('/calendar/events/{id}', [SmartCalendarController::class, 'destroyEvent'])->name('calendar.events.destroy');
    Route::delete('/calendar/schedules/{id}', [SmartCalendarController::class, 'destroySchedule'])->name('calendar.schedules.destroy');
    Route::post('/calendar/tasks', [SmartCalendarController::class, 'storeTask'])->name('calendar.tasks.store');
    Route::post('/tasks/{id}/complete', [SmartCalendarController::class, 'markTaskComplete'])->name('tasks.complete');

    // Productivity Logs
    Route::get('/dashboard/productivity', [ProductivityLogController::class, 'index'])->name('dashboard.productivity');

    // Dashboard views (statis)
    Route::get('/dashboard/focus', [DashboardController::class, 'focus'])->name('dashboard.focus');
    Route::get('/dashboard/academic', [DashboardController::class, 'academic'])->name('dashboard.academic');
    Route::get('/dashboard/pkl', [DashboardController::class, 'pkl'])->name('dashboard.pkl');

    // Livewire Component Routes
    Route::get('/dashboard/livewire/today-priority', TodayPriority::class)->name('livewire.today-priority');
    Route::get('/dashboard/livewire/productivity', ProductivityDashboard::class)->name('livewire.productivity');
    Route::get('/dashboard/livewire/finance', FinanceDashboard::class)->name('livewire.finance');
    Route::get('/dashboard/livewire/assets', AssetsDashboard::class)->name('livewire.assets');
    Route::get('/dashboard/livewire/debts', DebtsDashboard::class)->name('livewire.debts');
    Route::get('/dashboard/livewire/investments', InvestmentsDashboard::class)->name('livewire.investments');
    Route::get('/dashboard/livewire/schedule', ScheduleManager::class)->name('livewire.schedule');
    Route::get('/dashboard/livewire/tasks', TaskManager::class)->name('livewire.tasks');
    Route::get('/dashboard/livewire/settings', UserSettings::class)->name('livewire.settings');

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

    Route::post('/investments',             [InvestmentController::class, 'storeInstrument'])->name('investments.instruments.store');
    Route::get('/investments/{id}',        [InvestmentController::class, 'showInstrument'])->name('investments.instruments.show');
    Route::put('/investments/{id}',        [InvestmentController::class, 'updateInstrument'])->name('investments.instruments.update');
    Route::patch('/investments/{id}/price',  [InvestmentController::class, 'updatePrice'])->name('investments.instruments.price');
    Route::delete('/investments/{id}',        [InvestmentController::class, 'destroyInstrument'])->name('investments.instruments.destroy');

    Route::post('/investments/{id}/purchases', [InvestmentController::class, 'storePurchase'])->name('investments.purchases.store');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UsersController::class);
    Route::post('/users/{user}/preferences', [UsersController::class, 'updatePreferences'])->name('users.preferences');
});
