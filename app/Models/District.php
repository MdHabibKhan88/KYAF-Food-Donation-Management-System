<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class District extends Model
{
    use HasFactory;

    protected $table = 'districts';

    protected $primaryKey = 'district_id';

    public $timestamps = false;

    protected $fillable = [
        'division_name',
        'name_en',
        'name_bn',
    ];

    public function upazilas()
    {
        return $this->hasMany(
            Upazila::class,
            'district_id',
            'district_id'
        );
    }
}
