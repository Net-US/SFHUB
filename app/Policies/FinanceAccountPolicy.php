<?php
// ============================================================
// app/Policies/FinanceAccountPolicy.php
// ============================================================
namespace App\Policies;

use App\Models\FinanceAccount;
use App\Models\User;

class FinanceAccountPolicy
{
    public function update(User $user, FinanceAccount $account): bool
    {
        return $user->id === $account->user_id;
    }
    public function delete(User $user, FinanceAccount $account): bool
    {
        return $user->id === $account->user_id;
    }
}



// ============================================================
// Daftarkan di app/Providers/AuthServiceProvider.php:
// ============================================================
/*
protected $policies = [
    \App\Models\FinanceAccount::class => \App\Policies\FinanceAccountPolicy::class,
    \App\Models\Transaction::class    => \App\Policies\TransactionPolicy::class,
    \App\Models\SavingsGoal::class    => \App\Policies\SavingsGoalPolicy::class,
    \App\Models\Budget::class         => \App\Policies\BudgetPolicy::class,
    \App\Models\PendingNeed::class    => \App\Policies\PendingNeedPolicy::class,
];
*/
