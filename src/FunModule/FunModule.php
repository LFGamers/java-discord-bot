<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\FunModule;

use Discord\Base\AbstractModule;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * FunModule Class
 */
class FunModule extends AbstractModule
{
    /**
     * @return bool
     */
    public static function isDefaultEnabled()
    {
        return true;
    }
}
