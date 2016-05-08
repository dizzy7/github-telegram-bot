<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    const DEFAULT_MESSAGE = 'Please sent a link to repository which new version releases you want to get notifications 
    about (for example: https://github.com/symfony/symfony/releases)';

    /**
     * @Route("/{prefix}")
     */
    public function messageAction(Request $request, $prefix)
    {
        $configPrefix = $this->getParameter('telegram_webhook_prefix');

        if ($prefix !== $configPrefix) {
            throw $this->createNotFoundException();
        }

        $data = json_decode($request->getContent(), true);
        $user = $this->findUser($data['message']['from']['id'], $data['message']['from']['username']);
        $text = $data['message']['text'];

        $botApi = $this->get('app.telegram.api');

        $commands = $this->get('app.command_chain')->getCommands();
        foreach ($commands as $command) {
            if ($command->isCommandMatch($text)) {
                $response = $command->execute($user, $text);

                $botApi->sendMessage($user->getId(), $response);

                return new JsonResponse(['ok' => true]);
            }
        }

        $botApi->sendMessage($user->getId(), self::DEFAULT_MESSAGE);

        return new JsonResponse(['ok' => true]);
    }

    /**
     * @return User
     */
    private function findUser($id, $name)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->find(User::class, $id);

        if ($user === null) {
            $user = new User();
            $user->setId($id);
            $user->name = $name;
            $em->persist($user);
            $em->flush();
        }

        return $user;
    }
}