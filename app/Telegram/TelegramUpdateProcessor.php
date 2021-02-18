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

    public $user_id;

    public $user;

    const USER_STATES = [
        'default' => 0,
        'awaitingResponseForWelcomeMessage' => 1,
    ];


    public function __construct($data)
    {
        $this->first_name = $data['message']['chat']['first_name'];
        $this->username = $data['message']['chat']['username'];
        $this->chat_id = $data['message']['chat']['id'];
        $this->text = $data['message']['text'];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->first_name,
            'username' => $this->username,
            'chat_id' => $this->chat_id,
            'text' => $this->text,
        ];
    }

    public function realizesNewUser(): bool
    {
        return !User::where('username', $this->username)->exists();
    }

    public function processUpdate()
    {
        if ($this->realizesNewUser()) {
            $user = User::create([
                'name' => $this->first_name,
                'username' => $this->username,
            ]);

            $this->sendWelcomeMessage($this->toArray(), $user);

            $user->state = 'awaitingResponseForWelcomeMessage';
            $user->save();
        } else {
            $user = User::where('username', $this->username)->first();
            $this->user_id = $user->id;

            $stateIndex = self::USER_STATES[$user->state];

            switch ($stateIndex) {
                case 0:
                    $skillInfo = $this->parseSkillInfo();

                    if ($skillInfo) {
                        $user->skills()->create($skillInfo);

                        $this->sendDefaultSkillSuccessMessage($this->toArray(), $user);
                    } else {
                        $this->sendWrongFormatSkillMessage($this->toArray(), $user);
                    }
                    break;
                case 1:
                    $skillInfo = $this->parseSkillInfo();

                    if ($skillInfo) {
                        $user->skills()->create($skillInfo);
                        $user->state = 'default';
                        $user->save();

                        $this->sendFirstSkillSuccessMessage($this->toArray(), $user);

                    } else {
                        $this->sendWrongFormatSkillMessage($this->toArray(), $user);
                    }

                    break;

            }
        }
    }

    public function parseSkillInfo(): ?array
    {
        $skill = explode(':', $this->text);

        if (count($skill) == 2 && strlen($skill[0]) > 0 && strlen($skill[1]) > 0) {
            return [
                'name' => $skill[0],
                'description' => $skill[1],
            ];
        } else {
            return null;
        }
    }
}