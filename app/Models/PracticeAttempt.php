<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticeAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','practice_key','duration_seconds','errors_count','correct_count',
        'total_count','score','passed','details'
    ];

    protected $casts = [
        'passed' => 'boolean',
        'details' => 'array',
    ];
}
