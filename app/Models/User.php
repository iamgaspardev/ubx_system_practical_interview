<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'device_ids',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'device_ids' => 'array',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the user's formatted creation date
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('F j, Y');
    }

    /**
     * Get the user's initials for avatar
     */
    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach ($words as $word) {
            $initials .= strtoupper($word[0]);
            if (strlen($initials) >= 2)
                break;
        }

        return $initials ?: strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Get the profile image URL or null (OPTIONAL)
     */
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image && file_exists(public_path('storage/' . $this->profile_image))) {
            return asset('storage/' . $this->profile_image);
        }

        return null;
    }

    /**
     * Check if user has a profile image (OPTIONAL)
     */
    public function hasProfileImage()
    {
        return $this->profile_image && file_exists(public_path('storage/' . $this->profile_image));
    }

    /**
     * Get avatar for display (image or initials)
     */
    public function getAvatarAttribute()
    {
        return $this->hasProfileImage() ? $this->profile_image_url : $this->initials;
    }

    /**
     * Get the count of registered devices
     */
    public function getDeviceCountAttribute()
    {
        return $this->device_ids ? count($this->device_ids) : 0;
    }

    /**
     * Check if device ID is registered for this user
     */
    public function hasDeviceId($deviceId)
    {
        return $this->device_ids ? in_array($deviceId, $this->device_ids) : false;
    }
}