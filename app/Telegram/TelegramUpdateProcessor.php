<?php


namespace App\Telegram;

use App\Models\Skill;
use App\Models\User;
use App\Models\UserSkillAssessment;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;

class TelegramUpdateProcessor
{
    use BotCore;

    public $first_name;
    public $username;
    public $chat_id;
    public $text;
    public $sticker;
    public $message_id;
    public $callback_query;

    public $user;

    const USER_STATES = [
        1 => 'intro_step_1_waiting_for_initial_category_name',
        2 => 'intro_step_2_waiting_for_category_click_from_list',
        3 => 'intro_step_3_waiting_for_new_skill_click_button',
        4 => 'intro_step_4_waiting_for_new_skill',
        5 => 'default',
        6 => 'waiting_for_new_skill',
        7 => 'waiting_for_new_category',
    ];

    const BOT_COMMANDS = [
        '/start' => 0,
        '/info' => 1,
        '/categories' => 2,
        '/newcategory' => 3,
        '/vote' => 4,
        '/stats' => 5,
    ];


    public function __construct($data)
    {
        $this->first_name = $data['message']['chat']['first_name'] ?? $data['callback_query']['message']['chat']['first_name'] ?? '';
        $this->username = $data['message']['chat']['username'] ?? $data['callback_query']['message']['chat']['username'] ?? '';
        $this->chat_id = $data['message']['chat']['id'] ?? $data['callback_query']['message']['chat']['id'] ?? '';
        $this->text = $data['message']['text'] ?? $data['callback_query']['message']['text'] ?? '';
        $this->sticker = $data['message']['sticker'] ?? '';
        $this->callback_query = $data['callback_query'] ?? '';
        $this->message_id = $data['message']['message_id'] ?? $data['callback_query']['message']['message_id'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->first_name,
            'username' => $this->username,
            'chat_id' => $this->chat_id,
            'text' => $this->text,
            'message_id' => $this->message_id,
        ];
    }

    public function processUpdate()
    {
        $userRepository = new UserRepository();

        if ($this->realizesNewUser()) {
            $data = [
                'name' => $this->first_name,
                'username' => $this->username,
                'chat_id' => $this->chat_id,
            ];

            $user = $userRepository->addNewUser($data);

            $this->sendWelcomeMessage($this->toArray(), $user);

            return [];
        }

        $this->user = $userRepository->getUserByChatId($this->chat_id);

        if ($this->realizesBotCommand()) {

            $commandIndex = isset(self::BOT_COMMANDS[$this->text]) ? self::BOT_COMMANDS[$this->text] : 'unknown';

            switch ($commandIndex) {
                case 'unknown':
                    $this->sendUnknownCommandMessage($this->toArray(), []);
                    break;
                case 0:
                    $this->sendWeKnowEachOtherMessage($this->toArray(), []);
                    break;
                case 1:
                    $this->sendCurrentDevStatusMessage($this->toArray(), []);
                    break;
                case 2:
                    $this->sendCategoriesList($this->toArray(), $this->user->categories()->withCount('skills')->get());
                    break;
                case 3:
                    $this->sendAskingForNewCategoryNameMessage($this->toArray());
                    $userRepository->changeUserState($this->user, 7);
                    break;
                case 4:
                    if ($this->user->daily_votes_amount <= 5) {

                        $skillForVote = $userRepository->getAvailableSkillForVoting($this->user);

                        if ($skillForVote) {
                            $this->sendSkillVoteMessage($this->toArray(), $skillForVote);
                        } else {
                            $this->sendNoSkillsAreAvailableForVoting($this->toArray());
                        }
                    }
                    break;
                case 5:

                    $totalPTS = $userRepository->getTotalPoints($this->user);
                    $category = $userRepository->getCategoryWithHighestPoints($this->user);
                    $skill = $userRepository->getSkillWithHighestPoints($this->user);

                    $this->sendStatsMessage($this->toArray(),$totalPTS, $category, $skill);
            }

            return [];
        }

        if ($this->realizesSticker()) {
            $this->sendStickerRecognitionSoonMessage($this->toArray(), []);
            return [];
        }

        if ($this->realizesUserState()) {

            $state = self::USER_STATES[$this->user->state];

            switch ($state) {
                case 'intro_step_1_waiting_for_initial_category_name':
                    if ($this->userTextIsValid()) {
                        $this->user->categories()->create([
                            'title' => $this->text,
                        ]);

                        $userCategories = $this->user->categories()->withCount('skills')->get();

                        $this->sendFirstCategorySuccessMessage($this->toArray(), $userCategories);

                        $userRepository->changeUserState($this->user, 2);
                    }
                    break;
                case 'intro_step_2_waiting_for_category_click_from_list':
                    if ($this->isValidQueryCallback()) {
                        $skills = $userRepository->getUserCategoryById($this->user, $this->callback_query['data']);

                        $this->editPreviousMessageAndShowSkillList($this->callback_query, $skills);

                        $userRepository->changeUserState($this->user, 3);
                        $userRepository->changeUserLastClickedCategoryId($this->user, $this->callback_query['data']);

                    }
                    break;
                case 'intro_step_3_waiting_for_new_skill_click_button' :
                    if ($this->isValidQueryCallback()) {
                        $this->sendAskingForNewSkillMessage($this->toArray());

                        $userRepository->changeUserState($this->user, 4);
                    }
                    break;
                case 'intro_step_4_waiting_for_new_skill' :
                    if ($this->userTextIsValid()) {
                        $category = $this->user->categories()->where('id',
                            $this->user->last_clicked_category_id)->first();

                        $category->skills()->create([
                            'description' => $this->text
                        ]);

                        $userCategories = $this->user->categories()->withCount('skills')->get();

                        $this->sendIntroFinishedMessage($this->toArray(), $userCategories);

                        $userRepository->changeUserState($this->user, 5);
                    }
                    break;
                case 'default':
                    if ($this->callback_query != null) {
                        if (is_numeric($this->callback_query['data'])) {
                            $userRepository->changeUserLastClickedCategoryId($this->user,
                                $this->callback_query['data']);

                            $category = $userRepository->getUserCategoryById($this->user,
                                $this->callback_query['data']);

                            $this->sendSkillsList($this->toArray(), $category);
                        }
                        if ($this->callback_query['data'] == 'addNewSkill') {
                            $userRepository->changeUserState($this->user, 6);

                            $this->sendAskingForNewSkillMessage($this->toArray());
                        }
                        if ($this->callback_query['data'] == 'goBackToCategoriesList') {
                            $userRepository->changeUserState($this->user, 5);

                            $userCategories = $userRepository->getUserCategories($this->user);

                            $this->editLastBotMessageAndSendThereCategoriesList($this->toArray(), $userCategories);
                        }

                        if ($this->callback_query['data'] == 'continueVoting') {
                            $skillForVote = $userRepository->getAvailableSkillForVoting($this->user);

                            if ($skillForVote) {
                                $this->sendSkillVoteMessage($this->toArray(), $skillForVote);
                            } else {
                                $this->sendNoSkillsAreAvailableForVoting($this->toArray());
                            }
                        }

                        $json = $this->parseCallBackDataToJson();

                        if ($json) {
                            if (isset($json->skillId) && ($json->vote)) {
                                $skill = Skill::find($json->skillId);
                                $skill->assessment_points += $json->vote;
                                $skill->save();

                                $category = $skill->categories()->first();
                                $category->assessment_points += $json->vote;
                                $category->save();

                                $this->user->total_points += $json->vote;
                                $this->user->daily_votes_amount += 1;
                                $this->user->save();

                                $usa = new UserSkillAssessment();

                                $usa->user_id = $this->user->id;
                                $usa->skill_id = $skill->id;
                                $usa->point = $json->vote;

                                $usa->save();

                                $this->sendMessageWannaContinueVoting($this->toArray(),
                                    $this->user->daily_votes_amount);
                            }
                        }

                    }
                    break;
                case 'waiting_for_new_skill':
                    if ($this->userTextIsValid()) {
                        $category = $userRepository->getUserCategoryById($this->user,
                            $this->user->last_clicked_category_id);

                        $category->skills()->create([
                            'description' => $this->text
                        ]);

                        $userCategories = $userRepository->getUserCategories($this->user);

                        $this->sendNewSkillAdded($this->toArray(), $userCategories);

                        $userRepository->changeUserState($this->user, 5);
                    }
                    break;
                case 'waiting_for_new_category':
                    if ($this->userTextIsValid()) {
                        $this->user->categories()->create([
                            'title' => $this->text,
                        ]);

                        $userCategories = $userRepository->getUserCategories($this->user);

                        $this->newCategoryAdded($this->toArray(), $userCategories);

                        $userRepository->changeUserState($this->user, 5);
                    }
                    break;
            }
        }
    }

    public function isValidQueryCallback(): bool
    {
        if ($this->callback_query != null) {
            if (isset($this->callback_query['data'])) {
                return true;
            }
        }
        return false;
    }

    public function userTextIsValid(): bool
    {
        if ($this->text != null) {
            if (!ctype_space($this->text)) {
                return true;
            }
        }
        return false;
    }

    public function realizesBotCommand(): bool
    {
        if (substr($this->text, 0, 1) == '/') {
            return true;
        } else {
            return false;
        }
    }

    public function realizesNewUser(): bool
    {
        return !User::where('chat_id', $this->chat_id)->exists();
    }

    public function realizesUserState()
    {
        return User::where('username', $this->username)->first()->state;
    }

    public function realizesSticker(): bool
    {
        return $this->sticker != null;
    }

    public function parseCallbackDataToJson()
    {
        return json_decode($this->callback_query['data']);
    }
}
