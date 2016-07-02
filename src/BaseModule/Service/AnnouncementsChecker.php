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

use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Doctrine\Common\Persistence\ObjectManager;
use LFGamers\Discord\Helper\ConfigHelper;
use LFGamers\Discord\Model\Announcement;
use LFGamers\Discord\Model\Config;
use LFGamers\Discord\Model\Server;
use LFGamers\Discord\ServerManager;
use Monolog\Logger;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * PrivateChannelChecker Class
 */
class AnnouncementsChecker
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
     * @var ServerManager
     */
    private $serverManager;

    /**
     * PrivateChannelChecker constructor.
     *
     * @param Discord       $discord
     * @param ObjectManager $manager
     * @param ConfigHelper  $configHelper
     * @param Logger        $logger
     * @param ServerManager $serverManager
     */
    public function __construct(
        Discord $discord,
        ObjectManager $manager,
        ConfigHelper $configHelper,
        Logger $logger,
        ServerManager $serverManager
    ) {
        $this->discord       = $discord;
        $this->manager       = $manager;
        $this->configHelper  = $configHelper;
        $this->logger        = $logger;
        $this->serverManager = $serverManager;

        $this->discord->loop->addPeriodicTimer(30, [$this, 'checkAnnouncements']);
        $this->checkAnnouncements();
    }

    private function debug(...$messages)
    {
        //call_user_func_array([$this->logger, 'debug'], $messages);
    }

    /**
     * @return null|void
     */
    public function checkAnnouncements()
    {
        /** @var Server $dbServer */
        $dbServer = $this->serverManager->getDatabaseServer();
        $config   = $this->getConfig();
        $this->manager->refresh($dbServer);
        /** @var Channel $channel */
        $server = $this->serverManager->getClientServer();

        if (!$dbServer->isAnnouncementsEnabled()) {
            return;
        }

        $this->debug("Checking announcements: ".$this->serverManager->getClientServer()->name);

        if ($dbServer->getAnnouncementsChannel() === null) {
            return;
        }

        $channel = $server->channels->get('id', $dbServer->getAnnouncementsChannel());
        if ($dbServer->getLastAnnouncementMessage() === null) {
            return $this->sendAnnouncement($dbServer, $server, $channel);
        }

        if (empty($channel)) {
            return $this->logger->error(
                sprintf(
                    "Announcements channel for %s is non-existant.",
                    $server->name
                )
            );
        }

        $channel->getMessage($dbServer->getLastAnnouncementMessage())
            ->then(
                function (Message $message = null) use ($config, $dbServer, $server, $channel) {
                    if ($message === null) {
                        return $this->sendAnnouncement($dbServer, $server, $channel);
                    }
                    $this->debug(
                        'Last message was '.$message->timestamp->diffForHumans().' (Req '.$config['frequency'].'s)'
                    );
                    if ($message->timestamp->diffInSeconds() < (int) $config['frequency']) {
                        return;
                    }

                    $channel->getMessageHistory(
                        ['after' => $message, 'cache' => false, 'limit' => (int) $config['minimum_messages']]
                    )
                        ->then(
                            function (Collection $messages) use ($config, $dbServer, $server, $channel) {
                                $this->debug(
                                    $messages->count().' messages since last. (Req '.$config['minimum_messages'].')'
                                );
                                if ($messages->count() >= $config['minimum_messages']) {
                                    $this->sendAnnouncement($dbServer, $server, $channel);
                                }
                            }
                        );
                }
            )
            ->otherwise(
                function ($e) use ($dbServer, $server, $channel) {
                    $this->logger->error('Error getting message', ['exception' => $e]);
                    //$this->sendAnnouncement($dbServer, $server, $channel);
                }
            );
    }

    /**
     * @param Server  $dbServer
     * @param Guild   $server
     * @param Channel $channel
     *
     * @return null
     */
    private function sendAnnouncement(Server $dbServer, Guild $server, Channel $channel)
    {
        /*
        $this->logger->debug(
            sprintf(
                "Sending announcement to %s - #%s",
                $server->name,
                $channel->name
            )
        );
        */

        $announcement = $this->getRandomAnnouncement($dbServer);
        $channel->sendMessage("Announcement: \n\n".$announcement->getContent())
            ->then(
                function (Message $message) use ($dbServer) {
                    $dbServer->setLastAnnouncementMessage($message->id);
                    $this->manager->flush($dbServer);
                }
            );
    }

    /**
     * @param Server $server
     *
     * @return Announcement
     */
    private function getRandomAnnouncement(Server $server)
    {
        $this->manager->refresh($server);
        $announcements = $server->getAnnouncements();

        $keys = [];
        foreach ($announcements as $i => $announcement) {
            for ($u = 0; $u <= $announcement->getPriority(); $u++) {
                $keys[] = $i;
            }
        }

        $random = $announcements[$keys[rand(0, count($keys) - 1)]];
        $this->manager->refresh($random);

        return $random;
    }

    /**
     * @return array
     */
    private function getConfig()
    {
        $repo = $this->manager->getRepository(Config::class);

        $key    = 'announcements.'.$this->serverManager->getClientServer()->id;
        $config = $repo->findOneBy(['key' => $key]);
        if (empty($config)) {
            $config = new Config($key, '{}');
            $this->manager->persist($config);
            $this->manager->flush($config);
        }

        return json_decode($config->getValue(), true);
    }

    private function getMessagesSince(Message $message)
    {
    }
}
