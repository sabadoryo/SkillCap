<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Telegram\BotCore;
use App\Telegram\TelegramUpdateProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Telegram;

class TelegramBotController extends Controller
{
    protected $telegram;

    use BotCore;

    public function __construct(Telegram $telegram)
    {
        $this->telegram = $telegram;
    }

    public function getUpdate(Request $request)
    {
        $data = $request->all();

        Log::info(json_encode($data));

        $telegram_update_processor = new TelegramUpdateProcessor($data);

        $telegram_update_processor->processUpdate();

        return true;

    }

    public function sendNotification()
    {
        $users = User::all();

        foreach ($users as $user) {
            $this->sendNotificationMessage($user->chat_id, 'И так баги исправлены, отмечаем)00');
        }
    }
}
