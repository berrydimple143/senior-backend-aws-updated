<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCompanion extends Model
{
    use HasFactory;

    protected $table = "user_companions";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'companion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
