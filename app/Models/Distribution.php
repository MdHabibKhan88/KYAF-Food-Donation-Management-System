<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    protected $primaryKey = 'distribution_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['scheduled_start' => 'datetime', 'scheduled_end' => 'datetime', 'completed_at' => 'datetime', 'is_public' => 'boolean'];

    public function donation()
    {
        return $this->belongsTo(Donation::class, 'donation_id', 'donation_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class, 'thana_id', 'thana_id');
    }
}
