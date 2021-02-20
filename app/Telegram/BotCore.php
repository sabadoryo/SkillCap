<?php


namespace App\Telegram;


use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Request as TelegramRequest;

trait BotCore
{
    public function sendWelcomeMessage($data, $user)
    {
        $hiMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Приветствую, я <b>SkillBot</b>\nМоя единственная задача, это собирать данные о твоем нарастающем скилле. Ты можешь поделиться со мной и я буду вести отчет!\nЧто интересное или новое ты отркыл для себя сегодня? Это может быть все-что угодно, но для начала придумай категорию, в котором будет хранится этот скилл и все похожие с той же классификацией.",
            'parse_mode' => 'HTML'
        ]);


        if ($hiMessage->isOk()) {
            TelegramRequest::sendMessage([
                'chat_id' => $data['chat_id'],
                'text' => "Введи название категории. Например я сегодня научилась готовить яищницу, и назвала бы категорию этого скилла <b>Готовка</b> или <b>Кухня</b> \nА вообще называй как хочешь😋",
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
            'text' => "Пока что я не знаю, чем отвечать на это :/, но зато скоро смогу распозновать твое настроение по стикеру :3"
        ]);
    }

    public function sendFirstCategorySuccessMessage($data, $userCategories)
    {

        $reply_markup = $this->generateReplyMarkupForCategories($userCategories);

        $congratulationMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Отлично!\n Теперь <b>".$data['text']."</b> есть в списке твоих категорий. Нажми на нее, чтобы посмотреть список скиллов в этой категории.",
            'reply_markup' => $reply_markup,
            'parse_mode' => 'HTML'
        ]);
    }

    public function generateReplyMarkupForCategories($items)
    {

        $itemsArray = array_map(function ($cat) {
            return [
                'text' => $cat['title'].'('.$cat['skills_count'].')',
                'callback_data' => $cat['id'],
            ];
        }, $items->toArray());

        $max_per_row = 2;
        $per_row = sqrt(count($itemsArray));
        $rows = array_chunk($itemsArray, $per_row === floor($per_row) ? $per_row : $max_per_row);
        $reply_markup = new InlineKeyboard(...$rows);

        return $reply_markup;
    }

    public function generateReplyMarkupForSkills($items)
    {
        $itemsArray = array_map(function ($cat) {
            return [
                'text' => $cat['description'],
                'callback_data' => 'none',
            ];
        }, $items->toArray());

        array_unshift($itemsArray, [
            'text' => 'Добавить',
            'callback_data' => 'addNewSkill',
        ]);

        $max_per_row = 1;
        $per_row = sqrt(count($itemsArray));
        $rows = array_chunk($itemsArray, $per_row === floor($per_row) ? $per_row : $max_per_row);
        $reply_markup = new InlineKeyboard(...$rows);

        return $reply_markup;

    }


    public function editPreviousMessageAndShowSkillList($data, $category)
    {
        $reply_markup = $this->generateReplyMarkupForSkills($category->skills);

        $result = TelegramRequest::editMessageText([
            'chat_id' => $data['message']['chat']['id'],
            'text' => 'Теперь попробуй добавить первый свой скилл в категорию '.$category->title.',нажми на кнопку добавить после чего введи описание своего навыка.',
            'message_id' => $data['message']['message_id']
        ]);

        TelegramRequest::editMessageReplyMarkup([
            'chat_id' => $data['message']['chat']['id'],
            'message_id' => $data['message']['message_id'],
            'reply_markup' => $reply_markup,
        ]);
    }

    public function sendAskingForNewSkillMessage($data)
    {
        $defaultMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Окей,отправь мне название нового скилла!"
        ]);
    }

    public function sendIntroFinishedMessage($data, $categories)
    {
        $reply_markup = $this->generateReplyMarkupForCategories($categories);

        $congratulationMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Чтож поздравляю тебя с первым навыком! Продолжай в том же духе чеееел",
            'reply_markup' => $reply_markup,
            'parse_mode' => 'HTML'
        ]);
    }

    public function sendSkillsList($data, $category)
    {
        $reply_markup = $this->generateReplyMarkupForSkills($category->skills);

        $result = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Ваши навыки в <b>".$category->title.'</b>:',
            'reply_markup' => $reply_markup,
            'parse_mode' => 'HTML'
        ]);
    }

    public function sendNewSkillAdded($data, $categories)
    {
        $reply_markup = $this->generateReplyMarkupForCategories($categories);

        $congratulationMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Новый скилл успешно добавлен!",
            'reply_markup' => $reply_markup,
            'parse_mode' => 'HTML'
        ]);
    }

    public function sendCategoriesList($data, $categories)
    {
        $reply_markup = $this->generateReplyMarkupForCategories($categories);

        $congratulationMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Ваши категории:",
            'reply_markup' => $reply_markup,
            'parse_mode' => 'HTML'
        ]);
    }

    public function sendAskingForNewCategoryNameMessage($data)
    {
        $defaultMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Введи название новой категории!"
        ]);
    }

    public function newCategoryAdded($data, $categories)
    {
        $reply_markup = $this->generateReplyMarkupForCategories($categories);

        $congratulationMessage = TelegramRequest::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => "Новая категория добавлена:",
            'reply_markup' => $reply_markup,
            'parse_mode' => 'HTML'
        ]);
    }
}
