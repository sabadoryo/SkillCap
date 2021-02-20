<?php


namespace App\Telegram;

use App\Models\User;
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

        if ($this->realizesNewUser()) {
            $user = User::create([
                'name' => $this->first_name,
                'username' => $this->username,
                'chat_id' => $this->chat_id,
            ]);

            $this->sendWelcomeMessage($this->toArray(), $user);

            $user->state = 1;
            $user->save();

            return [];
        }

        $this->user = User::where('chat_id', $this->chat_id)->first();

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
                    $this->user->state = 7;
                    $this->user->save();
                    break;

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

                        $this->user->state = 2;

                        $this->user->save();
                    }
                    break;
                case 'intro_step_2_waiting_for_category_click_from_list':
                    if ($this->isValidQueryCallback()) {
                        $skills = $this->user->categories()->where('id', $this->callback_query['data'])->first();
                        $this->editPreviousMessageAndShowSkillList($this->callback_query, $skills);

                        $this->user->state = 3;
                        $this->user->last_clicked_category_id = $this->callback_query['data'];

                        $this->user->save();
                    }
                    break;
                case 'intro_step_3_waiting_for_new_skill_click_button' :
                    if ($this->isValidQueryCallback()) {
                        $this->sendAskingForNewSkillMessage($this->toArray());

                        $this->user->state = 4;
                        $this->user->save();
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

                        $this->user->state = 5;
                        $this->user->save();
                    }
                    break;
                case 'default':
                    if ($this->callback_query != null) {
                        if (is_numeric($this->callback_query['data'])) {
                            $this->user->last_clicked_category_id = $this->callback_query['data'];
                            $this->user->save();

                            $category = $this->user->categories()->where('id', $this->callback_query['data'])->first();
                            $this->sendSkillsList($this->toArray(), $category);
                        }
                        if ($this->callback_query['data'] == 'addNewSkill') {
                            $this->user->state = 6;
                            $this->user->save();

                            $this->sendAskingForNewSkillMessage($this->toArray());
                        }
                    }
                    break;
                case 'waiting_for_new_skill':
                    if ($this->userTextIsValid()) {
                        $category = $this->user->categories()->where('id',
                            $this->user->last_clicked_category_id)->first();

                        $category->skills()->create([
                            'description' => $this->text
                        ]);

                        $userCategories = $this->user->categories()->withCount('skills')->get();

                        $this->sendNewSkillAdded($this->toArray(), $userCategories);

                        $this->user->state = 5;
                        $this->user->save();
                    }
                    break;
                case 'waiting_for_new_category':
                    if ($this->userTextIsValid()) {
                        $this->user->categories()->create([
                            'title' => $this->text,
                        ]);

                        $userCategories = $this->user->categories()->withCount('skills')->get();

                        $this->newCategoryAdded($this->toArray(), $userCategories);
                        $this->user->state = 5;
                        $this->user->save();
                    }
            }
        }
    }

    public function isValidQueryCallback()
    {
        if ($this->callback_query != null) {
            if (isset($this->callback_query['data'])) {
                return true;
            }
        }
        return false;
    }

    public function userTextIsValid()
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
        return !User::where('username', $this->username)->exists();
    }

    public function realizesUserState()
    {
        return User::where('username', $this->username)->first()->state;
    }

    public function realizesSticker()
    {
        return $this->sticker != null;
    }
}
