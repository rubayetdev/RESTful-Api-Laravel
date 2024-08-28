<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user,User $model){
        return $user->id === $model->id;
    }

    public function update(User $user,User $model){
        return $user->id === $model->id;
    }
}
