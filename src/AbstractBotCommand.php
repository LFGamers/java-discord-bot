<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord;

use Discord\Base\AbstractBotCommand as BaseAbstractBotCommand;
use LFGamers\Discord\Helper\ConfigHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * AbstractBotCommand Class
 */
abstract class AbstractBotCommand extends BaseAbstractBotCommand
{
    /**
     * @var ConfigHelper
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->config = $container->get('helper.config');
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->config->get($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function setConfig($key, $value)
    {
        return $this->config->set($key, $value);
    }
}
