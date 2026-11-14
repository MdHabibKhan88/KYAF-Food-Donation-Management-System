<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Thana extends Model
{
    use HasFactory;

    protected $table = 'thanas';

    protected $fillable = [
        'upazila_id',
        'name_en',
        'name_bn',
    ];

    public function upazila()
    {
        return $this->belongsTo(Upazila::class, 'upazila_id');
    }
}
