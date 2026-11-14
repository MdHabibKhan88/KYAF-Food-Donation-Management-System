<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    public $timestamps = true;
    const UPDATED_AT = null;
    protected $guarded = [];
}
