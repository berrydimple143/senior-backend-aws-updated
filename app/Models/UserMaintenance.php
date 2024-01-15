<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMaintenance extends Model
{
    use HasFactory;

    protected $table = "user_maintenances";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'medicine',
        'dosage',
        'quantity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
