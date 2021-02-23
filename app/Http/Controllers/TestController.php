<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class TestController extends Controller
{
    protected $telegram;

    public function __construct(Telegram $telegram)
    {
        $this->telegram = $telegram;
    }

    public function setWebhook()
    {
        Log::info(config('telegrambot.hook_url'));
        $result = $this->telegram->setWebhook(config('telegrambot.hook_url'));
        if ($result->isOk()) {
            echo $result->getDescription();
        }
    }

    public function removeWebhook()
    {
        $this->telegram->deleteWebhook();
    }

    public function sendHelloMessage()
    {

        $result = Request::sendMessage([
            'chat_id' => '424232165',
            'text' => 'hELLO',
        ]);

    }

    public function sendByeMessage()
    {
        $result = Request::sendMessage([
            'chat_id' => '424232165',
            'text' => 'bYE',
        ]);
    }

    public function getUpdate(\Illuminate\Http\Request $request)
    {
        Log::info($request->all());
        $result = Request::sendMessage([
            'chat_id' => '424232165',
            'text' => 'gotUDude',
        ]);
    }
}
