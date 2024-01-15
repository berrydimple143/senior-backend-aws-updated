<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserService extends Model
{
    use HasFactory;

    protected $table = "user_services";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'service',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
