<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'role_id';
    public $timestamps = false;
    protected $guarded = [];

    public const ADMIN = 'ADMIN';
    public const MANAGER = 'AREA_MANAGER';
    public const STAFF = 'STAFF';
    public const DONOR = 'DONOR';
}
