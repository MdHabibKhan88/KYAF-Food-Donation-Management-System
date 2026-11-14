<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/** Append-only (SRS data integrity rule). */
class DonationStatusHistory extends Model
{
    protected $primaryKey = 'history_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['changed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Status history is append-only.'));
        static::deleting(fn () => throw new LogicException('Status history is append-only.'));
    }

    public function changer()
    {
        return $this->belongsTo(UserAccount::class, 'changed_by', 'user_id');
    }
}
