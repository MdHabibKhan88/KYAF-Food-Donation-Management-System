<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Upazila extends Model
{
    use HasFactory;

    protected $table = 'upazilas';

    protected $primaryKey = 'upazila_id';

    public $timestamps = false;

    protected $fillable = [
        'district_id',
        'name_en',
        'name_bn',
    ];

    public function district()
    {
        return $this->belongsTo(
            District::class,
            'district_id',
            'district_id'
        );
    }

    public function thanas()
    {
        return $this->hasMany(
            Thana::class,
            'upazila_id',
            'upazila_id'
        );
    }
}
