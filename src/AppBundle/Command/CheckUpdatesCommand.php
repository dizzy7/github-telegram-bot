<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckUpdatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:check-updates');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commands = $this->getContainer()->get('app.command_chain')->getCommands();

        foreach ($commands as $command) {
            $command->checkUpdates();
        }
    }
}
