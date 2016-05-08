<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Shaygan\TelegramBotApiBundle\Type\Update;
use Shaygan\TelegramBotApiBundle\UpdateReceiver\UpdateReceiverInterface;
use TelegramBot\Api\BotApi;

class TelegramReciver implements UpdateReceiverInterface
{
    private $botApi;
    private $em;

    /** @var User */
    private $user;

    public function __construct(
        BotApi $botApi,
        EntityManager $em
    ) {
        $this->botApi = $botApi;
        $this->em = $em;
    }

    public function handleUpdate(Update $update)
    {
        $message = json_decode(json_encode($update->message), true);
        $this->findUser($message['chat']['id']);

        $messageText = trim($message['text']);

        if (is_numeric($messageText)) {
            $this->confirmRegistration($messageText, $message);
        } elseif ($this->user === null) {
            $this->botApi->sendMessage(
                $message['chat']['id'],
                'Для использования бота необходимо зарегистрироваться на сайте http://lf.dizzy.name'
            );
        } else {
            $command = trim($message['text']);
            $matches = [];

            $text = 'test';


            if (is_array($text)) {
                $chunks = array_chunk($text, 25);
                foreach ($chunks as $chunk) {
                    $this->botApi->sendMessage($message['chat']['id'], implode("\n", $chunk));
                }
            } else {
                $this->botApi->sendMessage($message['chat']['id'], $text);
            }
        }
    }

    private function findUser($telegramId)
    {
        $this->user = $this->em->getRepository('AppBundle:User')->findOneBy(['telegramId' => $telegramId]);
    }
}
