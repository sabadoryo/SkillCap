<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'state',
        'chat_id',
        'last_clicked_category_id',
        'total_points',
        'daily_votes_amount',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'user_category', 'user_id', 'category_id');
    }

    public function skills()
    {
        return $this->hasManyDeep(Skill::class,['user_category', Category::class, 'category_skill']);
    }

    public function assessedSkills()
    {
        return $this->belongsToMany(Skill::class,'user_skill_assessments', 'user_id', 'skill_id');
    }

    public function skillAssessments()
    {
        return $this->belongsToMany(UserSkillAssessment::class, 'user_skill_assessments', 'user_id', 'skill_id');
    }

}
