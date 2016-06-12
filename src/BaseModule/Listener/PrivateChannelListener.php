<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\BaseModule\Listener;

use Discord\Base\AppBundle\Event\ServerManagerLoaded;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Doctrine\Common\Persistence\ObjectManager;
use LFGamers\Discord\BaseModule\Service\PrivateChannelChecker;
use LFGamers\Discord\Model\PrivateChannel;
use LFGamers\Discord\ServerManager;
use Monolog\Logger;
use React\EventLoop\LoopInterface;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * BotReadyListener Class
 */
class PrivateChannelListener
{
    /**
     * @var Discord
     */
    private $discord;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ServerManager
     */
    private $serverManager;

    /**
     * @param Discord       $discord
     * @param ObjectManager $manager
     * @param Logger        $logger
     */
    public function __construct(Discord $discord, ObjectManager $manager, Logger $logger)
    {
        $this->discord = $discord;
        $this->manager = $manager;
        $this->logger  = $logger;
    }

    /**
     * @param ServerManagerLoaded $event
     */
    public function onServerManagerReady(ServerManagerLoaded $event)
    {
        new PrivateChannelChecker($this->discord, $this->manager, $this->logger, $event->getManager());
    }
}
