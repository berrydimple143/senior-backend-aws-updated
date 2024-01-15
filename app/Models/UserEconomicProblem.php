<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEconomicProblem extends Model
{
    use HasFactory;

    protected $table = "user_economic_problems";
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
