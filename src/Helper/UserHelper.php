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
use Discord\Parts\Guild\Role;
use Discord\Parts\Permissions\Permission;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;
use DusanKasan\Knapsack\Collection;
use LFGamers\Discord\Exception\MemberNotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @return Member
     * @throws MemberNotFoundException
     */
    public static function getMember(User $user, Guild $guild) : Member
    {
        $member = $guild->members->get('id', $user->id);
        if (empty($member)) {
            throw new MemberNotFoundException($user->id);
        }

        return $member;
    }
}
