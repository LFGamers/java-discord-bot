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

use Discord\Base\AppBundle\Manager\ServerManager as BaseServerManager;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use LFGamers\Discord\Model\Announcement;
use LFGamers\Discord\Model\Config;
use LFGamers\Discord\Model\Server;
use LFGamers\Discord\Model\User;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ServerManager Class
 */
class ServerManager extends BaseServerManager
{
    /**
     * @var TimerInterface
     */
    protected $announcementsTimer;

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        $repo = $this->getRepository(User::class);

        foreach ($this->clientServer->members as $member) {
            $user = $repo->findOneByIdentifier($member->id);
            if (!empty($user)) {
                continue;
            }

            $user = new User();
            $user->setIdentifier($member->id);
            $user->setServer($this->databaseServer);
            $this->getManager()->persist($user);
        }

        $this->getManager()->flush();

        /** @var Server $server */
        $server = $this->getDatabaseServer();
        if ($server->isAnnouncementsEnabled()) {
            $this->startAnnouncements();
        }
    }

    public function onMemberDelete(Member $member)
    {
        $this->logEvent(
            sprintf(
                ':x: **%s#%d** (%d) has left the server :x:',
                $member->username,
                $member->discriminator,
                $member->id
            )
        );
    }

    public function onMemberUpdate(Member $member, Discord $discord)
    {
        /** @var Member $oldUser */
        $oldUser = $discord->guilds->get('id', $this->clientServer->id)->members->get('id', $member->id);

        $type = null;
        if ($member->username !== $oldUser->username) {
            $type = 'name';
        }
        if ($member->nick !== $oldUser->nick) {
            $type = 'name';
        }

        if ($type === null) {
            return;
        }

        $this->logEvent(
            sprintf(
                ':warning: **%s#%d** (%d) has changed their %s to `%s` :x:',
                $oldUser->username,
                $member->discriminator,
                $member->id,
                $type,
                $member->username
            )
        );
    }

    /**
     * @param Member $member
     */
    public function onMemberCreate(Member $member)
    {
        $this->logEvent(
            sprintf(
                ':white_check_mark: **%s#%d** (%d) has joined the server :white_check_mark:',
                $member->username,
                $member->discriminator,
                $member->id
            )
        );

        $user = $this->getRepository(User::class)->findOneByIdentifier($member->id);
        if (!empty($user)) {
            return;
        }

        $user = new User();
        $user->setIdentifier($member->id);
        $user->setServer($this->databaseServer);
        $this->getManager()->persist($user);
        $this->getManager()->flush();
    }

    /**
     * @param string $message
     */
    private function logEvent(string $message)
    {
        if (!$this->container->getParameter('features')['event_log']['enabled']) {
            return;
        }

        /** @var Channel $channel */
        $channel = $this->clientServer->channels->get('name', 'event-log');
        if (empty($channel)) {
            return;
        }

        $channel->sendMessage($message);
    }

    public function startAnnouncements()
    {
        /** @var Config|array|null $config */
        $config = $this->container->get('helper.config')->get('announcements.'.$this->getClientServer()->id);
        if (empty($config)) {
            return;
        }
        $config = json_decode($config->getValue(), true);

        /** @var LoopInterface $loop */
        $loop = $this->discord->loop;
        $this->logger->info('Starting announcement timer, running every '.$config['frequency'].'s');
        $this->announcementsTimer = $loop->addPeriodicTimer($config['frequency'], [$this, 'displayAnnouncement']);
        $this->displayAnnouncement();
    }

    /**
     * @return \React\Promise\PromiseInterface|static
     */
    public function displayAnnouncement()
    {
        /** @var Config|array|null $config */
        $config = $this->container->get('helper.config')->get('announcements.'.$this->getClientServer()->id);
        if (empty($config)) {
            return;
        }
        $config = json_decode($config->getValue(), true);

        $this->logger->info('Displaying random announcement');
        $announcement = $this->getRandomAnnouncement();
        $this->getManager()->refresh($announcement);
        $channel = $this->getAnnouncementChannel();

        if ($announcement->getLastAnnouncement() !== null) {
            $opts = ['after' => $announcement->getLastAnnouncement(), 'cache' => false];

            return $channel->getMessageHistory($opts)
                ->then(
                    function (Collection $messages) use ($announcement, $channel, $config) {
                        if ($messages->count() < (int) $config['minimum_messages']) {
                            return;
                        }

                        $this->sendAnnouncement($channel, $announcement);
                    }
                )->otherwise(
                    function () use ($channel, $announcement) {
                        $this->sendAnnouncement($channel, $announcement);
                    }
                );
        }

        $this->sendAnnouncement($channel, $announcement);
    }

    /**
     * @param Channel      $channel
     * @param Announcement $announcement
     */
    private function sendAnnouncement(Channel $channel, Announcement $announcement)
    {
        $channel->sendMessage("Announcement: \n\n".$announcement->getContent())
            ->then(
                function (Message $message) use ($announcement) {
                    $announcement->setLastAnnouncement($message->id);
                    $this->getManager()->flush($announcement);
                }
            );
    }

    public function stopAnnouncements()
    {
        $this->announcementsTimer->cancel();
    }

    /**
     * @return Announcement
     */
    private function getRandomAnnouncement()
    {
        /** @var Server $server */
        $server = $this->getDatabaseServer();
        $this->getManager()->refresh($server);
        $announcements = $server->getAnnouncements();

        $keys = [];
        foreach ($announcements as $i => $announcement) {
            for ($u = 0; $u <= $announcement->getPriority(); $u++) {
                $keys[] = $i;
            }
        }

        $random = $announcements[$keys[rand(0, count($keys) - 1)]];
        $this->getManager()->refresh($random);

        return $random;
    }

    /**
     * @return Channel
     */
    private function getAnnouncementChannel()
    {
        /** @var Server $server */
        $server = $this->getDatabaseServer();

        return $this->getClientServer()->channels->get('id', $server->getAnnouncementsChannel());
    }
}
