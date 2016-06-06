<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\ModerationModule\BotCommand;

use Discord\Base\Request;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Permissions\ChannelPermission;
use Discord\Parts\Permissions\RolePermission;
use LFGamers\Discord\AbstractBotCommand;
use LFGamers\Discord\Helper\UserHelper;
use LFGamers\Discord\Model\User;
use LFGamers\Discord\Model\UserLog;

/**
 * @author Aaron Scherer <aaron@lfgame.rs>
 *
 * PrivateChannelBotCommand Class
 */
class PrivateChannelBotCommand extends AbstractBotCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('private')
            ->setDescription('Manages private voice channels')
            ->setHelp(
                <<<EOF
Use the following to manage private voice channels:

`private create <name> @user1 [@user2...]` to create a private voice channel allowing all tagged users to have access
`private add @user` to add a user to your private channel
`private delete [name]` to delete your voice channel. If admin will delete named channel
EOF
            );
    }

    /**
     * @return void
     */
    public function setHandlers()
    {
        $this->responds(
            '/^private(\s+)?(help)?$/i',
            function (Request $request) {
                $request->reply($this->getHelp());
            }
        );

        $this->responds('/^private create "([A-Za-z0-9-_ ]+)"/', [$this, 'createPrivateChannel']);
    }

    protected function createPrivateChannel(Request $request, array $matches)
    {
        $repo = $this->getManager()->getRepository(User::class);
        $user = $repo->findOneByIdentifier($request->getAuthor()->id);
        if (empty($user)) {
            $user = new User();
            $user->setIdentifier($request->getAuthor()->id);
            $user->setServer($request->getDatabaseServer());
            $this->getManager()->persist($user);
        }

        if (!empty($user->getPrivateChannel())) {
            return $request->reply("You already have one private channel. Delete it to create another.");
        }

        /** @var Channel $channel */
        $channel  = $this->discord->factory(Channel::class, ['name' => $matches[1], 'type' => Channel::TYPE_VOICE]);
        $everyone = $request->getServer()->roles->get('name', '@everyone');
        $perm     = new ChannelPermission(['connect' => true]);

        // Negate everyone
        $channel->setPermissions($everyone, null, $perm);

        // Allow author
        $channel->setPermissions(UserHelper::getMember($request->getAuthor(), $request->getServer()), $perm, null);

        // Allow mentioned
        foreach ($request->getMentions() as $mention) {
            $channel->setPermissions(UserHelper::getMember($mention, $request->getServer()), $perm, null);
        }

        $request->getServer()->channels->save($channel);
    }
}
