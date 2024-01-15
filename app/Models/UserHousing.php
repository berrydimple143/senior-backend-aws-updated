<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHousing extends Model
{
    use HasFactory;

    protected $table = "user_housings";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
