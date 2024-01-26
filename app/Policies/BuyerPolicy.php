<?php

namespace App\Policies;

use App\User;
use App\Buyer;
use App\Traits\AdminActions;
use Illuminate\Auth\Access\HandlesAuthorization;

class BuyerPolicy
{
    use HandlesAuthorization, AdminActions;

    // public function create(User $user) {
    //     //
    // }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    // public function viewAny(User $user) {
        
    // }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Buyer $buyer) {
        return $user->id === $buyer->id;
    }



    /**
     * Determine whether the user can purchase
     */
    public function purchase(User $user, Buyer $buyer) {
        return $user->id === $buyer->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Buyer  $buyer
     * @return mixed
     */
    // public function restore(User $user, Buyer $buyer) {
        
    // }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Buyer  $buyer
     * @return mixed
     */
    // public function forceDelete(User $user, Buyer $buyer) {
        
    // }
}
