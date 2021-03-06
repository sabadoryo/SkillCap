<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSkillAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'point',
        'user_id',
        'skill_id'
    ];
}
