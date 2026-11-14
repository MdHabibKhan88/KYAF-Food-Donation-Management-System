<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaManager extends Model
{
    protected $primaryKey = 'manager_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['appointed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }

    public function serviceArea()
    {
        return $this->hasOne(ServiceArea::class, 'manager_id', 'manager_id');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'manager_id', 'manager_id');
    }

    public function activeStaffCount(): int
    {
        return $this->staff()->where('status', 'Active')->count();
    }
}
