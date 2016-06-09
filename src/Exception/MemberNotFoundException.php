<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\Exception;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * MemberNotFoundException Class
 */
class MemberNotFoundException extends \Exception
{
    /**
     * MemberNotFoundException constructor.
     *
     * @param int $userId
     */
    public function __construct(int $userId)
    {
        parent::__construct('User not found with that id. ('.$userId.')', 404);
    }
}
