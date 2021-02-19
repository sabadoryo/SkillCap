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
            'text' => "Кажется не правильный формат повтори пожалуйста! \n <b>SkillCategory : SkillDescription</b>",
            'parse_mode' => 'HTML'
        ]);

        TelegramRequest::sendSticker([
            'chat_id' => $data['chat_id'],
            'sticker' => 'CAACAgIAAxkBAAPHYC663QABQ2nOT7Ay4iKQyj7eO8avAAIFAAN1UIETZmBnin0s48QeBA'
        ]);

    }

    public function sendDefaultSkillSuccessMessage($data, $user)
    {
        $defaultMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Записал! Продолжай прокачиваться🚀"
        ]);

        $result = TelegramRequest::sendSticker([
            'chat_id' => $data['chat_id'],
            'sticker' => 'CAACAgIAAxkBAAN3YC6PmqXOgqXKI7IX0XLBKSgC9w4AAggAA3VQgRM_fvm4Yh7Dhh4E'
        ]);

    }

    public function sendUnknownCommandMessage($data, $user)
    {
        $defaultMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Хым, я пока не знаю такой команды, но я обещаю научиться"
        ]);

        $result = TelegramRequest::sendSticker([
            'chat_id' => $data['chat_id'],
            'sticker' => 'CAACAgIAAxkBAAO6YC64kfs5vx_bCtSF4DERHesRa0AAAvkmAAJLagMAASSlgZE1pac6HgQ'
        ]);

    }

    public function sendCurrentDevStatusMessage($data, $user)
    {
        $defaultMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Пока что, бот находиться на очень ранней стадии разработки.🌞 \nВ скором будущем будет обнова, <b>с новыми фичами(контроль категорий, уведомление, спринты)</b> \nА ну самое интересное у каждого пользователя будет свой <i>iqScore</i>😱 \nПока что, это загадка<s>(я сам даже не знаю, что это)</s>",
            'parse_mode' => 'HTML'
        ]);

        $result = TelegramRequest::sendSticker([
            'chat_id' => $data['chat_id'],
            'sticker' => 'CAACAgIAAxkBAAO5YC62Gw3LkTpMlf_g0ptseBeQXjwAAl8rAAJLagMAARqBSyqErwe8HgQ'
        ]);
    }

    public function sendWeKnowEachOtherMessage($data, $user)
    {
        $defaultMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Прив)"
        ]);
    }

    public function sendStickerRecognitionSoonMessage($data, $user)
    {
        $defaultMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Пока что я не знаю, чем отвечать на стикеры :/, но скоро смогу распозновать твое настроение по стикеру"
        ]);
    }
}
