<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMediaController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CreativeStudioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\FocusController;
use App\Http\Controllers\GeneralTrackerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\PklController;
use App\Http\Controllers\ProductivityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SmartCalendarController;
use Illuminate\Support\Facades\Route;


// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'submitContactForm'])->name('contact.submit');

Route::post('/feedback', [HomeController::class, 'storeFeedback'])->name('feedback.store');

// Public Blog Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::prefix('midtrans')->group(function () {
    Route::post('/webhook', [MidtransController::class, 'webhook'])->name('midtrans.webhook');
    Route::get('/finish',   [MidtransController::class, 'finish'])->name('midtrans.finish');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Google OAuth
    Route::get('/auth/google', [\App\Http\Controllers\GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleController::class, 'callback'])->name('auth.google.callback');
});

// Protected Routes (require authentication)
Route::middleware('auth')->group(function () {
    // Buat transaksi Midtrans
    Route::post('/subscribe', [MidtransController::class, 'createTransaction'])->name('subscribe');

    Route::get('/onboarding/payment', [AuthController::class, 'showOnboardingPayment'])
        ->name('auth.onboarding-payment');
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
    Route::get('/dashboard/tracker', [GeneralTrackerController::class, 'index'])->name('dashboard.tracker');

    // ════════════════════════════════════════════════════════════════════════
    // GENERAL TRACKER - UPDATED dengan route yang lebih bersih
    // ════════════════════════════════════════════════════════════════════════
    Route::post('/tasks',             [GeneralTrackerController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/quick-add',   [GeneralTrackerController::class, 'quickAdd'])->name('tasks.quick-add');
    Route::post('/tasks/{id}/status', [GeneralTrackerController::class, 'updateStatus'])->name('tasks.update-status');
    Route::delete('/tasks/{id}',      [GeneralTrackerController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{id}/complete', [GeneralTrackerController::class, 'completeTask'])->name('tasks.complete');

    // ═══════════════════════════════════════════════════════════════════════
    // CREATIVE STUDIO (Content Creator Tasks)
    // ═══════════════════════════════════════════════════════════════════════
    Route::prefix('dashboard/creative')->name('dashboard.creative.')->group(function () {
        // Halaman utama
        Route::get('/', [CreativeStudioController::class, 'index'])->name('index');

        // Task Management
        Route::post('/', [CreativeStudioController::class, 'store'])->name('store');
        Route::get('/task/{id}', [CreativeStudioController::class, 'showTaskDetail'])->name('task.show');
        Route::put('/{id}', [CreativeStudioController::class, 'update'])->name('update');
        Route::delete('/{id}', [CreativeStudioController::class, 'destroy'])->name('destroy');

        // Status & Actions
        Route::post('/{id}/status', [CreativeStudioController::class, 'updateStatus'])->name('status.update');
        Route::post('/{id}/done', [CreativeStudioController::class, 'markDone'])->name('mark-done');
        Route::post('/{id}/reschedule', [CreativeStudioController::class, 'reschedule'])->name('reschedule');
        Route::post('/{id}/links', [CreativeStudioController::class, 'addLink'])->name('add-link');

        // Subtasks
        Route::post('/task/{taskId}/subtask', [CreativeStudioController::class, 'storeSubtask'])->name('subtask.store');
        Route::put('/task/{taskId}/subtask/{subtaskId}', [CreativeStudioController::class, 'updateSubtask'])->name('subtask.update');
        Route::delete('/task/{taskId}/subtask/{subtaskId}', [CreativeStudioController::class, 'destroySubtask'])->name('subtask.destroy');
        Route::post('/task/{taskId}/create-default-subtasks', [CreativeStudioController::class, 'createDefaultSubtasks'])->name('subtask.create-default');
    });

    // ════════════════════════════════════════════════════════════════════════
    // SMART CALENDAR - UPDATED dengan route updateSchedule
    // ════════════════════════════════════════════════════════════════════════
    Route::get('/dashboard/smart-calendar', [SmartCalendarController::class, 'index'])->name('dashboard.smartcalendar');
    Route::get('/calendar/day/{date}',      [SmartCalendarController::class, 'showDay'])->name('calendar.day');

    // Events (one-off)
    Route::post('/calendar/events',          [SmartCalendarController::class, 'storeEvent'])->name('calendar.events.store');
    Route::put('/calendar/events/{id}',      [SmartCalendarController::class, 'updateEvent'])->name('calendar.events.update');
    Route::delete('/calendar/events/{id}',   [SmartCalendarController::class, 'destroyEvent'])->name('calendar.events.destroy');

    // Schedules (recurring)
    Route::post('/calendar/schedules',       [SmartCalendarController::class, 'storeSchedule'])->name('calendar.schedules.store');
    Route::put('/calendar/schedules/{id}',   [SmartCalendarController::class, 'updateSchedule'])->name('calendar.schedules.update');
    Route::delete('/calendar/schedules/{id}', [SmartCalendarController::class, 'destroySchedule'])->name('calendar.schedules.destroy');

    // Calendar Tasks
    Route::post('/calendar/tasks',           [SmartCalendarController::class, 'storeTask'])->name('calendar.tasks.store');

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

        // Session Material Management
        Route::post('/sessions/{id}/material', [AcademicController::class, 'updateSessionMaterial'])->name('sessions.material');
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

    // ══════════════════════════════════════════════════════════════════
    // INDODAX INTEGRATION
    // ══════════════════════════════════════════════════════════════════
    Route::post('/indodax/connect',     [\App\Http\Controllers\IndodaxController::class, 'store'])->name('indodax.connect');
    Route::post('/indodax/test',        [\App\Http\Controllers\IndodaxController::class, 'test'])->name('indodax.test');
    Route::post('/indodax/sync',        [\App\Http\Controllers\IndodaxController::class, 'sync'])->name('indodax.sync');
    Route::get('/indodax/status',       [\App\Http\Controllers\IndodaxController::class, 'status'])->name('indodax.status');
    Route::delete('/indodax/disconnect', [\App\Http\Controllers\IndodaxController::class, 'disconnect'])->name('indodax.disconnect');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard & Analytics
    Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');

    // User Management (using new AdminUserController)
    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle');
    Route::get('/users-export', [AdminUserController::class, 'export'])->name('users.export');

    // Subscriptions
    // View (HTML)
    Route::get('/subscriptions', [SubscriptionController::class, 'getSubscriptions'])
        ->name('subscriptions');

    // Plan CRUD (JSON)
    Route::get('/subscriptions/plans',               [SubscriptionController::class, 'getPlans'])
        ->name('subscriptions.plans.index');
    Route::get('/subscriptions/plans/{plan}',        [SubscriptionController::class, 'getPlan'])
        ->name('subscriptions.plans.show');
    Route::post('/subscriptions/plans',              [SubscriptionController::class, 'storePlan'])
        ->name('subscriptions.store');
    Route::put('/subscriptions/plans/{plan}',        [SubscriptionController::class, 'updatePlan'])
        ->name('subscriptions.plans.update');
    Route::patch('/subscriptions/plans/{plan}/toggle', [SubscriptionController::class, 'togglePlan'])
        ->name('subscriptions.plans.toggle');
    Route::delete('/subscriptions/plans/{plan}',     [SubscriptionController::class, 'destroyPlan'])
        ->name('subscriptions.plans.destroy');

    // Subscription management (JSON)
    Route::get('/subscriptions/stats',                         [SubscriptionController::class, 'getStats'])
        ->name('subscriptions.stats');
    Route::post('/subscriptions/{subscription}/cancel',        [SubscriptionController::class, 'cancelSubscription'])
        ->name('subscriptions.cancel');
    Route::post('/subscriptions/{subscription}/extend',        [SubscriptionController::class, 'extendSubscription'])
        ->name('subscriptions.extend');

    // Blog Management
    Route::get('/blog', [AdminBlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/create', [AdminBlogController::class, 'create'])->name('blog.create');
    Route::post('/blog', [AdminBlogController::class, 'store'])->name('blog.store');
    Route::get('/blog/{post}/edit', [AdminBlogController::class, 'edit'])->name('blog.edit');
    Route::get('/blog/{post}', [AdminBlogController::class, 'show'])->name('blog.show');
    Route::put('/blog/{post}', [AdminBlogController::class, 'update'])->name('blog.update');
    Route::delete('/blog/{post}', [AdminBlogController::class, 'destroy'])->name('blog.destroy');
    Route::get('/blog-categories', [AdminBlogController::class, 'getCategories'])->name('blog.categories');
    Route::post('/blog-categories', [AdminBlogController::class, 'storeCategory'])->name('blog.categories.store');
    Route::put('/blog-categories/{category}', [AdminBlogController::class, 'updateCategory'])->name('blog.categories.update');
    Route::delete('/blog-categories/{category}', [AdminBlogController::class, 'destroyCategory'])->name('blog.categories.destroy');
    Route::get('/blog-tags', [AdminBlogController::class, 'getTags'])->name('blog.tags');
    Route::post('/blog-tags', [AdminBlogController::class, 'storeTag'])->name('blog.tags.store');
    Route::get('/blog/comments', [AdminBlogController::class, 'getComments'])->name('blog.comments');
    Route::post('/blog/comments', [AdminBlogController::class, 'storeComment'])->name('blog.comments.store');
    Route::delete('/blog/comments/{comment}', [AdminBlogController::class, 'destroyComment'])->name('blog.comments.destroy');

    // FAQ Management
    Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
    Route::post('/faq', [FaqController::class, 'store'])->name('faq.store');
    Route::get('/faq/{faq}', [FaqController::class, 'show'])->name('faq.show');
    Route::put('/faq/{faq}', [FaqController::class, 'update'])->name('faq.update');
    Route::delete('/faq/{faq}', [FaqController::class, 'destroy'])->name('faq.destroy');
    Route::patch('/faq/{faq}/toggle', [FaqController::class, 'toggle'])->name('faq.toggle');
    Route::post('/faq-categories', [FaqController::class, 'storeCategory'])->name('faq.categories.store');
    Route::put('/faq-categories/{category}', [FaqController::class, 'updateCategory'])->name('faq.categories.update');
    Route::delete('/faq-categories/{category}', [FaqController::class, 'destroyCategory'])->name('faq.categories.destroy');

    // Landing Page Management
    Route::get('/landing', [LandingPageController::class, 'index'])->name('landing');
    Route::post('/landing/save-all', [LandingPageController::class, 'saveAll'])->name('landing.save');
    Route::post('/landing/upload-image', [LandingPageController::class, 'uploadHeroImage'])->name('landing.upload-image');
    Route::post('/landing/upload-logo', [LandingPageController::class, 'uploadLogo'])->name('landing.upload-logo');
    Route::post('/landing/upload-favicon', [LandingPageController::class, 'uploadFavicon'])->name('landing.upload-favicon');
    Route::post('/landing/delete-hero-image', [LandingPageController::class, 'deleteHeroImage'])->name('landing.delete-hero-image');
    Route::post('/landing/delete-logo', [LandingPageController::class, 'deleteLogo'])->name('landing.delete-logo');
    Route::post('/landing/delete-favicon', [LandingPageController::class, 'deleteFavicon'])->name('landing.delete-favicon');
    Route::get('/landing/heroes', [LandingPageController::class, 'getHeroes'])->name('landing.heroes');
    Route::post('/landing/heroes', [LandingPageController::class, 'storeHero'])->name('landing.heroes.store');
    Route::put('/landing/heroes/{hero}', [LandingPageController::class, 'updateHero'])->name('landing.heroes.update');
    Route::delete('/landing/heroes/{hero}', [LandingPageController::class, 'destroyHero'])->name('landing.heroes.destroy');
    Route::get('/landing/features', [LandingPageController::class, 'getFeatures'])->name('landing.features');
    Route::post('/landing/features', [LandingPageController::class, 'storeFeature'])->name('landing.features.store');
    Route::put('/landing/features/{feature}', [LandingPageController::class, 'updateFeature'])->name('landing.features.update');
    Route::delete('/landing/features/{feature}', [LandingPageController::class, 'destroyFeature'])->name('landing.features.destroy');
    Route::post('/landing/features/reorder', [LandingPageController::class, 'reorderFeatures'])->name('landing.features.reorder');
    Route::get('/landing/testimonials', [LandingPageController::class, 'getTestimonials'])->name('landing.testimonials');
    Route::post('/landing/testimonials', [LandingPageController::class, 'storeTestimonial'])->name('landing.testimonials.store');
    Route::put('/landing/testimonials/{testimonial}', [LandingPageController::class, 'updateTestimonial'])->name('landing.testimonials.update');
    Route::delete('/landing/testimonials/{testimonial}', [LandingPageController::class, 'destroyTestimonial'])->name('landing.testimonials.destroy');
    Route::get('/landing/stats', [LandingPageController::class, 'getStats'])->name('landing.stats');
    Route::post('/landing/stats', [LandingPageController::class, 'storeStat'])->name('landing.stats.store');
    Route::put('/landing/stats/{stat}', [LandingPageController::class, 'updateStat'])->name('landing.stats.update');
    Route::delete('/landing/stats/{stat}', [LandingPageController::class, 'destroyStat'])->name('landing.stats.destroy');

    // SEO Management
    Route::get('/seo', [SeoController::class, 'index'])->name('seo.index');
    Route::put('/seo/global', [SeoController::class, 'updateGlobalSettings'])->name('seo.global.update');
    Route::get('/seo/pages', [SeoController::class, 'getPageSettings'])->name('seo.pages');
    Route::put('/seo/pages/{seoSetting}', [SeoController::class, 'updatePageSettings'])->name('seo.pages.update');
    Route::get('/seo/meta-tags', [SeoController::class, 'getMetaTags'])->name('seo.meta');
    Route::post('/seo/meta-tags', [SeoController::class, 'storeMetaTag'])->name('seo.meta.store');
    Route::put('/seo/meta-tags/{metaTag}', [SeoController::class, 'updateMetaTag'])->name('seo.meta.update');
    Route::delete('/seo/meta-tags/{metaTag}', [SeoController::class, 'destroyMetaTag'])->name('seo.meta.destroy');
    Route::get('/seo/sitemap', [SeoController::class, 'getSitemapSettings'])->name('seo.sitemap');
    Route::put('/seo/sitemap', [SeoController::class, 'updateSitemapSettings'])->name('seo.sitemap.update');
    Route::post('/seo/sitemap/generate', [SeoController::class, 'generateSitemap'])->name('seo.sitemap.generate');
    Route::get('/seo/sitemap/download', [SeoController::class, 'downloadSitemap'])->name('seo.sitemap.download');

    // Static Pages Management
    Route::get('/seo/pages/create', [SeoController::class, 'createPage'])->name('seo.pages.create');
    Route::post('/seo/pages', [SeoController::class, 'storePage'])->name('seo.pages.store');
    Route::get('/seo/pages/{page}/edit', [SeoController::class, 'editPage'])->name('seo.pages.edit');
    Route::put('/seo/pages/{page}', [SeoController::class, 'updatePage'])->name('seo.static-pages.update');
    Route::delete('/seo/pages/{page}', [SeoController::class, 'destroyPage'])->name('seo.pages.destroy');
    Route::get('/api/pages', [SeoController::class, 'getPages'])->name('api.pages');

    // System Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
    Route::put('/settings/{setting}', [SettingController::class, 'update'])->name('settings.update');
    Route::delete('/settings/{setting}', [SettingController::class, 'destroy'])->name('settings.destroy');
    Route::get('/settings/group/{group}', [SettingController::class, 'getByGroup'])->name('settings.group');
    Route::post('/settings/clear-cache', [SettingController::class, 'clearCache'])->name('settings.cache.clear');
    Route::get('/settings/system-info', [SettingController::class, 'getSystemInfo'])->name('settings.system-info');
    Route::post('/settings/save', [SettingController::class, 'saveSettings'])->name('settings.save');
    Route::post('/settings/test-email', [SettingController::class, 'testEmail'])->name('settings.test-email');

    // Backup Management
    Route::get('/settings/backups', [SettingController::class, 'backups'])->name('settings.backups');
    Route::post('/backups/create', [SettingController::class, 'createBackup'])->name('backups.create');
    Route::post('/settings/backups', [SettingController::class, 'createBackup'])->name('settings.backups.store');
    Route::get('/settings/backups/{backup}/download', [SettingController::class, 'downloadBackup'])->name('settings.backups.download');
    Route::delete('/settings/backups/{backup}', [SettingController::class, 'destroyBackup'])->name('settings.backups.destroy');
    Route::post('/backups/config', [SettingController::class, 'configBackup'])->name('backups.config');

    // Activity Logs
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/activity/{activityLog}', [LogController::class, 'showActivityLog'])->name('logs.activity.show');
    Route::get('/logs/system', [LogController::class, 'getSystemLogs'])->name('logs.system');
    Route::delete('/logs/activity', [LogController::class, 'clearActivityLogs'])->name('logs.activity.clear');
    Route::delete('/logs/system', [LogController::class, 'clearSystemLogs'])->name('logs.system.clear');
    Route::get('/logs/stats', [LogController::class, 'getLogStats'])->name('logs.stats');
    Route::get('/logs/export', [LogController::class, 'exportLogs'])->name('logs.export');
    Route::post('/logs/clear', [LogController::class, 'clearAllLogs'])->name('logs.clear');

    // Roles & Permissions Management
    Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/list', [AdminRoleController::class, 'getRoles'])->name('roles.list');
    Route::post('/roles', [AdminRoleController::class, 'storeRole'])->name('roles.store');
    Route::put('/roles/{role}', [AdminRoleController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{role}', [AdminRoleController::class, 'deleteRole'])->name('roles.destroy');
    Route::post('/roles/seed', [AdminRoleController::class, 'seedDefaults'])->name('roles.seed');

    // Permissions
    Route::get('/permissions', [AdminRoleController::class, 'getPermissions'])->name('permissions.list');
    Route::post('/permissions', [AdminRoleController::class, 'storePermission'])->name('permissions.store');
    Route::put('/permissions/{permission}', [AdminRoleController::class, 'updatePermission'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [AdminRoleController::class, 'deletePermission'])->name('permissions.destroy');

    // User Role Assignment
    Route::get('/users/{user}/roles', [AdminRoleController::class, 'getUserRoles'])->name('users.roles');
    Route::post('/users/{user}/roles', [AdminRoleController::class, 'assignRoleToUser'])->name('users.roles.assign');

    // Media / File Manager
    Route::get('/media', [AdminMediaController::class, 'index'])->name('media.index');
    Route::get('/media/list', [AdminMediaController::class, 'getMedia'])->name('media.list');
    Route::post('/media/upload', [AdminMediaController::class, 'upload'])->name('media.upload');
    Route::put('/media/{media}', [AdminMediaController::class, 'update'])->name('media.update');
    Route::delete('/media/{media}', [AdminMediaController::class, 'destroy'])->name('media.destroy');
    Route::post('/media/bulk-delete', [AdminMediaController::class, 'bulkDestroy'])->name('media.bulk-destroy');
    Route::get('/media/folders', [AdminMediaController::class, 'getFolders'])->name('media.folders');
    Route::post('/media/folders', [AdminMediaController::class, 'createFolder'])->name('media.folders.store');
    Route::get('/media/stats', [AdminMediaController::class, 'getStats'])->name('media.stats');
});
