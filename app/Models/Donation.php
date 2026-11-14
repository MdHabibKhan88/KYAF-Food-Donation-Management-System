<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $primaryKey = 'donation_id';
    const CREATED_AT = 'submitted_at';
    const UPDATED_AT = 'updated_at';
    protected $guarded = [];
    protected $casts = ['expiry_date' => 'date', 'submitted_at' => 'datetime', 'updated_at' => 'datetime'];

    public const FINAL = ['Completed', 'Rejected', 'Cancelled', 'Expired'];

    public function donor()
    {
        return $this->belongsTo(Donor::class, 'donor_id', 'donor_id');
    }

    public function area()
    {
        return $this->belongsTo(ServiceArea::class, 'area_id', 'area_id');
    }

    public function pickupThana()
    {
        return $this->belongsTo(Thana::class, 'pickup_thana_id', 'thana_id');
    }

    public function assignedStaff()
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id', 'staff_id');
    }

    public function histories()
    {
        return $this->hasMany(DonationStatusHistory::class, 'donation_id', 'donation_id')->orderBy('changed_at')->orderBy('history_id');
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class, 'donation_id', 'donation_id');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'donation_id', 'donation_id');
    }

    public function collection()
    {
        return $this->hasOne(Collection::class, 'donation_id', 'donation_id');
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class, 'donation_id', 'donation_id');
    }

    /** Expiry date and time are entered in Bangladesh time; return the instant in UTC. */
    public function expiryInstant(): Carbon
    {
        return Carbon::parse($this->expiry_date->format('Y-m-d').' '.$this->expiry_time, config('app.display_timezone'))->utc();
    }

    public function isExpired(): bool
    {
        return now()->greaterThanOrEqualTo($this->expiryInstant());
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL, true);
    }

    public function statusLabel(): string
    {
        return __('kyaf.status.'.$this->status);
    }

    /** Verified meal quantity (latest successful verification). */
    public function verifiedQuantity(): ?int
    {
        return $this->verifications()->where('result', 'Verified')->latest('verification_id')->value('verified_quantity_meals');
    }
}
