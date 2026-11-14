<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class District extends Model
{
    use HasFactory;

    protected $table = 'districts';

    protected $fillable = [
        'division_id',
        'name_en',
        'name_bn',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function upazilas()
    {
        return $this->hasMany(Upazila::class, 'district_id');
    }
}
