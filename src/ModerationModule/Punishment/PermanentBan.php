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

use Discord\Parts\User\Member;
use LFGamers\Discord\Model\Strike;
use React\Promise\FulfilledPromise;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * PermanentBan Class
 */
class PermanentBan extends AbstractPunishment
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
        return new FulfilledPromise();
    }
}
