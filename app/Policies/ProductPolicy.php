<?php

namespace App\Policies;

use App\Product;
use App\Traits\AdminActions;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization, AdminActions;


    // Determine whether the user can add category. 
    public function addCategory(User $user, Product $product) {
        return $user->id === $product->seller->id;
    }

    // Determine whether the user can delete the category.  
    public function deleteCategory(User $user, Product $product) {
        return $user->id === $product->seller->id;
    }

}
