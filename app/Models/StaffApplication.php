<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffApplication extends Model
{
    protected $primaryKey = 'application_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['date_of_birth' => 'date', 'submitted_at' => 'datetime', 'reviewed_at' => 'datetime', 'invitation_expires_at' => 'datetime'];

    public function thana()
    {
        return $this->belongsTo(Thana::class, 'thana_id', 'thana_id');
    }

    public function area()
    {
        return $this->belongsTo(ServiceArea::class, 'area_id', 'area_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(UserAccount::class, 'reviewed_by', 'user_id');
    }
}
