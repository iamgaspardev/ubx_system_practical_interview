<?php

namespace App\Policies;

use App\Models\DiamondData;
use App\Models\User;

class DiamondDataPolicy
{
    public function view(User $user, DiamondData $diamond)
    {
        return $user->id === $diamond->upload->user_id;
    }
}