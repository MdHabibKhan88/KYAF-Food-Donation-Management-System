<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $primaryKey = 'token_id';
    public $timestamps = true;
    const UPDATED_AT = null;
    protected $guarded = [];
    protected $casts = ['expires_at' => 'datetime', 'used_at' => 'datetime', 'created_at' => 'datetime'];
}
