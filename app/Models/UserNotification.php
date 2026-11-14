<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    public $timestamps = true;
    const UPDATED_AT = null;
    protected $guarded = [];
    protected $casts = ['is_read' => 'boolean', 'read_at' => 'datetime', 'created_at' => 'datetime'];

    public function donation()
    {
        return $this->belongsTo(Donation::class, 'donation_id', 'donation_id');
    }
}
