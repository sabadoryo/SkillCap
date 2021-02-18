<?php


namespace App\Telegram;


use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Request as TelegramRequest;

trait BotCore
{
    public function sendWelcomeMessage($data, $user)
    {
        $hiMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => 'Приветствую, я SkillBot. Моя единственная задача, это собирать данные о твоем нарастающем скилле. Ты можешь поделиться со мной и я буду вести отчет!',
        ]);

        if ($hiMessage->isOk()) {
            TelegramRequest::sendMessage([
                'chat_id' => $data['chat_id'],
                'text' => "Попробуй написать, то чему ты сегодня научился \nНапример я сегодня научился, как можно скачивать бесплатные игры😍 \nОтправляй в формате: \n<b>Школьная алгебра : Понял теорему дискриминанта</b> \n<b>Пиратство : Скачал игру в тридаблю.бесплтаныеигры.ру</b>",
                'parse_mode' => 'HTML'
            ]);

        }
    }

    public function sendFirstSkillSuccessMessage($data, $user)
    {
        $congratulationMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Огоо, круто! Тоже на днях попробую изучить это \n Но ты не теряй хватку, и продолжай в том же духе. Жду от тебя вестей каждый день!"
        ]);
    }

    public function sendWrongFormatSkillMessage($data, $user)
    {
        $wrongFormatMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Кажется не правильный формат повтори пожалуйста!"
        ]);

    }

    public function sendDefaultSkillSuccessMessage($data, $user)
    {
        $defaultMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Записал! Продолжай прокачиваться🚀"
        ]);
    }
}
