<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\BaseModule\DependencyInjection\Compiler;

use LFGamers\Discord\BaseModule\Factory\RequestFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\Tests\Fixtures\Reference;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * RequestFactoryCompilerPass Class
 */
class RequestFactoryCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('factory.request')
            ->setClass(RequestFactory::class)
            ->addArgument($container->getDefinition('helper.acl'));
    }
}
