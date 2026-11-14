<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceArea extends Model
{
    protected $primaryKey = 'area_id';
    public $timestamps = true;
    const UPDATED_AT = null;
    protected $guarded = [];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }

    public function manager()
    {
        return $this->belongsTo(AreaManager::class, 'manager_id', 'manager_id');
    }

    public function thanas()
    {
        return $this->hasMany(Thana::class, 'area_id', 'area_id');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'area_id', 'area_id');
    }
}
