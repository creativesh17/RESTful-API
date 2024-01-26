<?php

namespace App\Policies;

use App\Traits\AdminActions;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization, AdminActions;

    // Determine whether the user can view the model.
    public function view(User $activatedUser, User $user) {
        return $activatedUser->id === $user->id;
    }

    // Determine whether the user can update 
    public function update(User $activatedUser, User $user) {
        return $activatedUser->id === $user->id;
    }

    // Determine whether the user can delete 
    public function delete(User $activatedUser, User $user) {
        return $activatedUser->id === $user->id && $activatedUser->token()->client->personal_access_client;
    }

}
