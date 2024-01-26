<?php

namespace App\Traits;

use App\User;

trait AdminActions {

    public function before($user, $ability) {
        if($user->admin == User::ADMIN_USER) {
            return true;
        }
    }

}





