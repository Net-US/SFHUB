protected $policies = [
    \App\Models\FinanceAccount::class => \App\Policies\FinanceAccountPolicy::class,
    \App\Models\Transaction::class    => \App\Policies\TransactionPolicy::class,
    \App\Models\SavingsGoal::class    => \App\Policies\SavingsGoalPolicy::class,
    \App\Models\Budget::class         => \App\Policies\BudgetPolicy::class,
    \App\Models\PendingNeed::class    => \App\Policies\PendingNeedPolicy::class,
];
