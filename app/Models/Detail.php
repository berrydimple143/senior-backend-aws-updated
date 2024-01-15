<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    use HasFactory;

    protected $table = "user_details";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'birth_date',
        'religion',
        'blood_type',
        'education',
        'employment_status',
        'member_status',
        'civil_status',
        'gender',
        'identification',
        'language',
        'ethnic_origin',
        'able_to_travel',
        'active_in_politics',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
