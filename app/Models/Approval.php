<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $primaryKey = 'approval_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['decided_at' => 'datetime'];

    public function decider()
    {
        return $this->belongsTo(UserAccount::class, 'decided_by', 'user_id');
    }
}
