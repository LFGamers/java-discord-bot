<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\BaseModule\BotCommand;

use LFGamers\Discord\AbstractBotCommand;
use LFGamers\Discord\Request;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * InviteBotCommand Class
 */
class InviteBotCommand extends AbstractBotCommand
{
    public function configure()
    {
        $this->setName('invite')->setDescription('Gives you an invite URL for the bot');
    }

    public function setHandlers()
    {
        $this->responds(
            '/^invite$/',
            function (Request $request) {
                $request->reply('https://discordapp.com/oauth2/authorize?&client_id=176236669211639810&scope=bot');
            }
        );
    }

}
