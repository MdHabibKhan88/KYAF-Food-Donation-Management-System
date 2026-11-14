<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/** Append-only (SRS data integrity rule). */
class AuditLog extends Model
{
    protected $primaryKey = 'log_id';
    public $timestamps = true;
    const UPDATED_AT = null;
    protected $guarded = [];
    protected $casts = ['old_value' => 'array', 'new_value' => 'array', 'created_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit log is append-only.'));
        static::deleting(fn () => throw new LogicException('Audit log is append-only.'));
    }

    public function user()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }
}
