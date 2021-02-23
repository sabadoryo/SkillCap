<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description'
    ];

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'category_skill', 'category_id', 'skill_id');
    }
}
