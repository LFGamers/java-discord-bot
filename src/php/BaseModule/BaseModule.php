<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\BaseModule;

use Discord\Base\AbstractModule;
use LFGamers\Discord\BaseModule\DependencyInjection\Compiler\RequestFactoryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * BaseModule Class
 */
class BaseModule extends AbstractModule
{
    /**
     * @return bool
     */
    public static function isDefaultEnabled()
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function isDisableable()
    {
        return false;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RequestFactoryCompilerPass());
    }
}
