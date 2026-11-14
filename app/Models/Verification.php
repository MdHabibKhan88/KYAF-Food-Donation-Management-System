<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    protected $primaryKey = 'verification_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['verified_at' => 'datetime'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
