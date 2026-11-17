<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Thana extends Model
{
    use HasFactory;

    protected $table = 'thanas';

    protected $primaryKey = 'thana_id';

    public $timestamps = false;

    protected $fillable = [
        'upazila_id',
        'area_id',
        'name_en',
        'name_bn',
    ];

    public function upazila()
    {
        return $this->belongsTo(
            Upazila::class,
            'upazila_id',
            'upazila_id'
        );
    }

    public function serviceArea()
    {
        return $this->belongsTo(
            ServiceArea::class,
            'area_id',
            'area_id'
        );
    }
}
