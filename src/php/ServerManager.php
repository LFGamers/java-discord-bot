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

    /**
     * @param Member $member
     */
    public function onMemberCreate(Member $member)
    {
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
}
