<?php

namespace AppBundle\Service;

use AppBundle\Interfaces\TelegramCommand;

class CommandChain
{
    private $commands = [];

    /**
     * @return TelegramCommand[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    public function addCommand(TelegramCommand $command)
    {
        $this->commands[] = $command;
    }
}