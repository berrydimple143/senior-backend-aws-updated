<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChildren extends Model
{
    use HasFactory;

    protected $table = "user_children";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'full_name',
        'occupation',
        'income',
        'age',
        'dependency',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
