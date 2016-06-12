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
use Doctrine\Common\Persistence\ObjectManager;
use LFGamers\Discord\BaseModule\Service\AnnouncementsChecker;
use LFGamers\Discord\Helper\ConfigHelper;
use Monolog\Logger;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * BotReadyListener Class
 */
class AnnouncementsListener
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
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Discord       $discord
     * @param ObjectManager $manager
     * @param ConfigHelper  $configHelper
     * @param Logger        $logger
     */
    public function __construct(Discord $discord, ObjectManager $manager, ConfigHelper $configHelper, Logger $logger)
    {
        $this->discord      = $discord;
        $this->manager      = $manager;
        $this->configHelper = $configHelper;
        $this->logger       = $logger;
    }

    /**
     * @param ServerManagerLoaded $event
     */
    public function onServerManagerReady(ServerManagerLoaded $event)
    {
        new AnnouncementsChecker(
            $this->discord,
            $this->manager,
            $this->configHelper,
            $this->logger,
            $event->getManager()
        );
    }
}
