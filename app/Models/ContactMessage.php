<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $primaryKey = 'message_id';
    public $timestamps = true;
    const UPDATED_AT = null;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime'];

    public function handler()
    {
        return $this->belongsTo(UserAccount::class, 'handled_by', 'user_id');
    }
}
