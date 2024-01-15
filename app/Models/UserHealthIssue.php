<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHealthIssue extends Model
{
    use HasFactory;

    protected $table = "user_health_issues";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'problem',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
