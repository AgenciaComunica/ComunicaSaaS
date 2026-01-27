<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function deactivate(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->isActive() && $user->id !== $model->id;
    }
}
