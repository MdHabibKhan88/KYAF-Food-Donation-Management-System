<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class UserAccount extends Authenticatable
{
    protected $table = 'user_accounts';
    protected $primaryKey = 'user_id';
    protected $guarded = [];
    protected $hidden = ['password_hash'];
    protected $rememberTokenName = '';
    protected $casts = ['locked_until' => 'datetime', 'last_login_at' => 'datetime'];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function donor()
    {
        return $this->hasOne(Donor::class, 'user_id', 'user_id');
    }

    public function manager()
    {
        return $this->hasOne(AreaManager::class, 'user_id', 'user_id');
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'user_id', 'user_id');
    }

    public function notificationsList()
    {
        return $this->hasMany(UserNotification::class, 'user_id', 'user_id');
    }

    public function roleName(): string
    {
        return $this->role->role_name;
    }

    public function hasRole(string $name): bool
    {
        return $this->roleName() === $name;
    }
public function photoUrl(): ?string
{
    return $this->profile_photo_path
        ? route('profile.photo').'?v='.optional($this->updated_at)->timestamp
        : null;
}

public function initials(): string
{
    return mb_substr(trim($this->full_name), 0, 1);
}
    public function unreadCount(): int
    {
        return $this->notificationsList()->where('channel', 'InApp')->where('is_read', false)->count();
    }
}
