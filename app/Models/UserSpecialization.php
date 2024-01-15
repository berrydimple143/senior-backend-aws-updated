<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSpecialization extends Model
{
    use HasFactory;

    protected $table = "user_specializations";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'area',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
