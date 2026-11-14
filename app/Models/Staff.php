<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['joined_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }

    public function manager()
    {
        return $this->belongsTo(AreaManager::class, 'manager_id', 'manager_id');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'assigned_staff_id', 'staff_id');
    }
}
