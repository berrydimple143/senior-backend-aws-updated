<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFamily extends Model
{
    use HasFactory;

    protected $table = "user_families";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'spouse_first_name',
        'spouse_middle_name',
        'spouse_last_name',
        'spouse_extension_name',
        'father_first_name',
        'father_middle_name',
        'father_last_name',
        'father_extension_name',
        'mother_first_name',
        'mother_middle_name',
        'mother_last_name',
        'mother_extension_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
