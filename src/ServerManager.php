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
use Discord\Parts\Channel\Channel;
use Discord\Parts\User\Member;
use LFGamers\Discord\Model\Server;
use LFGamers\Discord\Model\User;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ServerManager Class
 */
class ServerManager extends BaseServerManager
{
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

    public function onMemberUpdate(Member $member, Discord $discord)
    {
        /** @var Member $oldUser */
        $oldUser = $discord->guilds->get('id', $this->clientServer->id)->members->get('id', $member->id);

        var_dump([$member->nick, $oldUser->nick]);
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

        $this->discord->ws->loop->addTimer(
            1,
            function () use ($channel, $message) {
                $channel->sendMessage($message);
            }
        );
    }
}