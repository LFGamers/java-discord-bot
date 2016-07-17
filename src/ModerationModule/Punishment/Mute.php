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
use LFGamers\Discord\Helper\RoleHelper;
use LFGamers\Discord\Model\Strike;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * Mute Class
 */
class Mute extends AbstractPunishment
{
    /**
     * {@inheritdoc}
     */
    public function perform(Strike $strike)
    {
        return $this->getMember(
            $strike,
            function (Member $member) {
                $member->addRole(RoleHelper::getRoleByName('Muted', $member->guild));
                $member->mute = true;
                $member->guild->members->save($member);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Strike $strike)
    {
        return $this->getMember(
            $strike,
            function (Member $member) {
                $member->removeRole(RoleHelper::getRoleByName('Muted', $member->guild));
                $member->mute = false;
                $member->guild->members->save($member);
            },
            function ($error) {
            }
        );
    }
}
