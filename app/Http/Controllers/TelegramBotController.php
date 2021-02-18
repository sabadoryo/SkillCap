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




//        if (!User::where('username', $data['message']['chat']['username'])->exists()) {
//            $user = new User();
//
//            $user->name = $data['message']['chat']['first_name'];
//            $user->username = $data['message']['chat']['username'];
//            $user->save();
//
//            $result = TelegramRequest::sendMessage([
//                'chat_id' => $data['message']['chat']['id'],
//                'text' => 'Приветствую чел,' . $data['message']['chat']['first_name'],
//            ]);
//
//            Log::info($result);
//
//
//        } else {
//            $result = TelegramRequest::sendMessage([
//                'chat_id' => $data['message']['chat']['id'],
//                'text' => 'С возвращением,' . $data['message']['chat']['first_name'],
//            ]);
//
//            Log::info($result);
//        }
    }
}
