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
use Discord\Base\Request;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use Discord\Parts\WebSockets\PresenceUpdate;
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

    /**
     * @param PresenceUpdate $presenceUpdate
     * @param PresenceUpdate $old
     */
    public function onPresenceUpdate(PresenceUpdate $presenceUpdate, PresenceUpdate $old)
    {
        if ($presenceUpdate->status === 'offline' && $presenceUpdate->status !== $old->status) {
            /** @var User $user */
            $user = $this->getRepository(User::class)->findOneBy(['identifier' => $presenceUpdate->user->id]);
            $user->setLastSeen(new \DateTime());
        }
    }

    /**
     * @param Request $request
     */
    protected function onMessage(Request $request)
    {
        $user = $this->getRepository(User::class)->findOneBy(['identifier' => $request->getAuthor()->id]);
        $user->setLastSeen(new \DateTime());
        $user->setLastSpoke(new \DateTime());

        parent::onMessage($request);
    }

    public function onMemberUpdate(Member $member, Member $oldUser)
    {
        $type = null;
        if ($member->username !== $oldUser->username) {
            $type = 'name';

            /** @var User $user */
            $user = $this->getRepository(User::class)->findOneBy(['identifier' => $member->id]);
            $user->addName($member->username);
        }
        if ($member->nick !== $oldUser->nick) {
            $type = 'nick';
        }

        if ($type === null) {
            return;
        }

        if ($type === 'nick' && $member->nick === null) {
            return $this->logEvent(
                sprintf(
                    ':warning: **%s#%d** (%d) has reset their nick :warning:',
                    $oldUser->username,
                    $member->discriminator,
                    $member->id
                )
            );
        }

        $this->logEvent(
            sprintf(
                ':warning: **%s#%d** (%d) has changed their %s to `%s` :warning:',
                $oldUser->username,
                $member->discriminator,
                $member->id,
                $type,
                $member->{$type}
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
        /** @var Channel $channel */
        $channel = $this->clientServer->channels->get('name', 'event-log');
        if (empty($channel)) {
            return;
        }

        $channel->sendMessage($message);
    }
}
