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

    }
}
