<?php

// ============================================================
// app/Policies/SavingsGoalPolicy.php
// ============================================================
namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;

class SavingsGoalPolicy
{
    public function update(User $user, SavingsGoal $goal): bool
    {
        return $user->id === $goal->user_id;
    }
    public function delete(User $user, SavingsGoal $goal): bool
    {
        return $user->id === $goal->user_id;
    }
}
