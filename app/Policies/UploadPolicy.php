<?php

namespace App\Policies;

use App\Models\Upload;
use App\Models\User;

class UploadPolicy
{
    public function view(User $user, Upload $upload)
    {
        return $user->id === $upload->user_id;
    }

    public function delete(User $user, Upload $upload)
    {
        return $user->id === $upload->user_id;
    }
}