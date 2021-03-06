<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'assessment_points',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_skill', 'skill_id', 'category_id');
    }
}
