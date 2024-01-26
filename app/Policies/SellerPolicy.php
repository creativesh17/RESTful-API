<?php

namespace App\Policies;

use App\Seller;
use App\Traits\AdminActions;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SellerPolicy
{
    use HandlesAuthorization, AdminActions;


    // Determine whether the user can view the model. 
    public function view(User $user, Seller $seller) {
        return $user->id === $seller->id;
    }

    // Determine whether the user can sell a product 
    public function sale(User $user, User $seller) {
        return $user->id === $seller->id;
    }

    // Determine whether the user can update a product 
    public function updateProduct(User $user, Seller $seller) {
        return $user->id === $seller->id;
    }

    // Determine whether the user can delete a product 
    public function deleteProduct(User $user, Seller $seller) {
        return $user->id === $seller->id;
    }

}
