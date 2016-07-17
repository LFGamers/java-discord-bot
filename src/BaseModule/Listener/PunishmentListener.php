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

use Discord\Base\AppBundle\Event\BotEvent;
use Discord\Base\AppBundle\Event\ServerManagerLoaded;
use Discord\Discord;
use Doctrine\Common\Persistence\ObjectManager;
use LFGamers\Discord\BaseModule\Service\PunishmentChecker;
use Monolog\Logger;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * BotReadyListener Class
 */
class PunishmentListener
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
     * @param Discord       $discord
     * @param ObjectManager $manager
     * @param Logger        $logger
     */
    public function __construct(Discord $discord, ObjectManager $manager, Logger $logger)
    {
        $this->discord      = $discord;
        $this->manager      = $manager;
        $this->logger       = $logger;
    }

    /**
     * @param BotEvent $event
     */
    public function onBotReady(BotEvent $event)
    {
        new PunishmentChecker($this->discord, $this->manager, $this->logger);
    }
}
