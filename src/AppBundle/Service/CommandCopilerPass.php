<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandCopilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('app.command_chain')) {
            return;
        }

        $definition = $container->findDefinition('app.command_chain');

        $taggedServices = $container->findTaggedServiceIds('app.telegram_command');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addCommand', [new Reference($id)]);
        }
    }
}