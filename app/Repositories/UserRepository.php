<?php


namespace App\Repositories;


use App\Models\Skill;
use App\Models\User;

class UserRepository
{
    public function addNewUser($data)
    {
        $user = User::create($data);

        $this->changeUserState($user, 1);

        return $user;
    }

    public function changeUserState($user, $newState)
    {
        $user->state = $newState;
        $user->save();
    }

    public function getUserByChatId($chat_id)
    {
        $user = User::where('chat_id', $chat_id)
            ->first();
        return $user;
    }

    public function changeUserLastClickedCategoryId($user, $lastClickedCategoryId)
    {
        $user->last_clicked_category_id = $lastClickedCategoryId;
        $user->save();
    }

    public function getUserCategoryById($user, $categoryId)
    {
        $category = $user->categories()
            ->where('id', $categoryId)
            ->first();

        return $category;
    }

    public function getUserCategories($user)
    {
        $userCategories = $user->categories()
            ->withCount('skills')
            ->get();

        return $userCategories;
    }

    public function getAvailableSkillForVoting($user)
    {
        $userSkillIds = $user->skills()->pluck('skills.id')->toArray();
        $userAlreadyVotedSkills = $user->assessedSkills()->pluck('skills.id')->toArray();

        $exceptSkillIds = array_merge($userAlreadyVotedSkills, $userSkillIds);

        $availableSkill = Skill::whereNotIn('id', $exceptSkillIds)->first();

        return $availableSkill;
    }

    public function getTotalPoints($user)
    {
        $totalPoints = $user->total_points;
        return $totalPoints;
    }

    public function getCategoryWithHighestPoints($user)
    {
        $highest_point_category = $user->categories()->max('assessment_points');
        return $highest_point_category;
    }

    public function getSkillWithHighestPoints($user)
    {
        $highest_point_skill = $user->skills()->max('assessment_points');
        return $highest_point_skill;
    }
}
