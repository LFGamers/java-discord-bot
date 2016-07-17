<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\Helper;

use Discord\Parts\Guild\Guild;
use Discord\Parts\User\User;
use React\Promise\FulfilledPromise;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * RoleHelper Class
 */
abstract class UserHelper
{
    /**
     * @param User  $user
     * @param Guild $guild
     *
     * @return Promise|PromiseInterface|FulfilledPromise
     */
    public static function getMember(User $user, Guild $guild)
    {
        return $guild->members->fetch($user->id);
    }
}
