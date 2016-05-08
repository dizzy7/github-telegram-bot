<?php

namespace AppBundle\Interfaces;

use AppBundle\Entity\User;

interface TelegramCommand
{
    public function isCommandMatch($command);
    
    public function execute(User $user, $command);
    
    public function checkUpdates();
}