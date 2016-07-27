<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\ModerationModule\Punishment;

use Discord\Parts\Guild\Ban;
use Discord\Parts\User\Member;
use LFGamers\Discord\Model\Strike;
use React\Promise\FulfilledPromise;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * TemporaryBan Class
 */
class TemporaryBan extends AbstractPunishment
{
    /**
     * {@inheritdoc}
     */
    public function perform(Strike $strike)
    {
        return $this->getMember(
            $strike,
            function (Member $member) {
                $member->ban(3);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Strike $strike)
    {
        $guild = $this->getGuild($strike);
        $ban   = $this->getBan($strike);
        $guild->bans->delete($ban);

        return new FulfilledPromise();
    }

    /**
     * @param Strike $strike
     *
     * @return Ban
     * @throws \Exception
     */
    protected function getBan(Strike $strike)
    {
        $guild = $this->getGuild($strike);
        foreach ($guild->bans as $ban) {
            if ($ban->user->id === (string) $strike->getUser()->getIdentifier()) {
                return $ban;
            }
        }

        throw new \Exception("User is not banned");
    }
}
