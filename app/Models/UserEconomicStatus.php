<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEconomicStatus extends Model
{
    use HasFactory;

    protected $table = "user_economic_statuses";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'source_of_income',
        'assets',
        'income_range',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
