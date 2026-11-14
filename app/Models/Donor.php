<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    protected $primaryKey = 'donor_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['registered_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(UserAccount::class, 'user_id', 'user_id');
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class, 'thana_id', 'thana_id');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'donor_id', 'donor_id');
    }

    /** DNR-NNNNNN, next sequential number. Call inside a transaction. */
    public static function nextId(): string
    {
        $last = static::query()->lockForUpdate()->orderByDesc('donor_id')->value('donor_id');
        $n = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'DNR-'.str_pad((string) $n, 6, '0', STR_PAD_LEFT);
    }
}
