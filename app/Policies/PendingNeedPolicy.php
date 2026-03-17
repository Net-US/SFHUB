<?php // ============================================================
// app/Policies/PendingNeedPolicy.php
// ============================================================
namespace App\Policies;

use App\Models\PendingNeed;
use App\Models\User;

class PendingNeedPolicy
{
    public function update(User $user, PendingNeed $need): bool
    {
        return $user->id === $need->user_id;
    }
    public function delete(User $user, PendingNeed $need): bool
    {
        return $user->id === $need->user_id;
    }
}
