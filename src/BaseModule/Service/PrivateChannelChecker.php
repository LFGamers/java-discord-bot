<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\BaseModule\Service;

use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Doctrine\Common\Persistence\ObjectManager;
use LFGamers\Discord\Model\PrivateChannel;
use LFGamers\Discord\ServerManager;
use Monolog\Logger;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * PrivateChannelChecker Class
 */
class PrivateChannelChecker
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
     * PrivateChannelChecker constructor.
     *
     * @param Discord       $discord
     * @param ObjectManager $manager
     * @param Logger        $logger
     * @param ServerManager $serverManager
     */
    public function __construct(Discord $discord, ObjectManager $manager, Logger $logger, ServerManager $serverManager)
    {
        $this->discord       = $discord;
        $this->manager       = $manager;
        $this->logger        = $logger;
        $this->serverManager = $serverManager;

        $this->discord->loop->addPeriodicTimer(5, [$this, 'checkServerChannels']);
        $this->checkServerChannels();
    }

    public function checkServerChannels()
    {
        $this->logger->info("Checking server channels: ".$this->serverManager->getClientServer()->name);
        $repo   = $this->manager->getRepository(PrivateChannel::class);
        $server = $this->serverManager->getClientServer();

        /** @var PrivateChannel $privateChannel */
        foreach ($repo->findAll() as $privateChannel) {
            if ($privateChannel->getServer()->getIdentifier() !== $server->id) {
                continue;
            }

            /** @var Channel $channel */
            $channel = $server->channels->get('id', $privateChannel->getChannelId());
            if (empty($channel) || !is_object($channel)) {
                $privateChannel->getUser()->setPrivateChannel(null);
                $this->manager->remove($privateChannel);
                continue;
            }

            $insert = Carbon::instance($privateChannel->getInsertDate());
            $this->logger->debug(
                sprintf(
                    "Checking: [%s] (%s) - %d users - %s",
                    $server->name,
                    $channel->name,
                    $channel->members->count(),
                    $insert->diffForHumans()
                )
            );

            if ($channel->members->count() > 0) {
                continue;
            }

            if ($insert->diffInMinutes() < 5) {
                continue;
            }

            $this->logger->info(sprintf("Deleting channel in '%s': '%s'", $server->name, $channel->name));
            $server->channels->delete($channel);
            $privateChannel->getUser()->setPrivateChannel(null);
            $this->manager->remove($privateChannel);
        }

        $this->manager->flush();
    }
}
