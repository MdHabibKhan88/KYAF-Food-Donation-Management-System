<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{
    use HasFactory;

    protected $table = 'divisions';

    protected $fillable = [
        'name_en',
        'name_bn',
    ];

    public function districts()
    {
        return $this->hasMany(District::class, 'division_id');
    }
}
