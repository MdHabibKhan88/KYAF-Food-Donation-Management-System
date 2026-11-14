<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $primaryKey = 'collection_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['scheduled_at' => 'datetime', 'collected_at' => 'datetime'];

    public function donation()
    {
        return $this->belongsTo(Donation::class, 'donation_id', 'donation_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
