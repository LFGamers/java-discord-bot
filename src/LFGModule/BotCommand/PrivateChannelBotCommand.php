<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\LFGModule\BotCommand;

use Discord\Base\Request;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Permissions\ChannelPermission;
use LFGamers\Discord\AbstractBotCommand;
use LFGamers\Discord\Helper\UserHelper;
use LFGamers\Discord\Model\PrivateChannel;
use LFGamers\Discord\Model\User;

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

`private <name> @user1 [@user2...]` to create a private voice channel allowing all tagged users to have access
`private add @user` to add a user to your private channel
`private remove @user` to remove a user from your private channel
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

        $this->responds('/^private delete/', [$this, 'deletePrivateChannel']);
        $this->responds('/^private add/', [$this, 'addMemberToPrivateChannel']);
        $this->responds('/^private remove/', [$this, 'removeMemberFromPrivateChannel']);
        $this->responds('/^private (?<name>[A-Za-z0-9-_ ]+)/', [$this, 'createPrivateChannel']);
    }

    protected function deletePrivateChannel(Request $request, array $matches)
    {
        $this->logger->info("Creating private channel");
        $repo = $this->getManager()->getRepository(User::class);

        /** @var User $user */
        $user = $repo->findOneByIdentifier($request->getAuthor()->id);
        if (empty($user)) {
            $user = new User();
            $user->setIdentifier($request->getAuthor()->id);
            $user->setServer($request->getDatabaseServer());
            $this->getManager()->persist($user);
            $this->getManager()->flush($user);
        }

        if (empty($user->getPrivateChannel())) {
            return $request->reply("You don't have a private voice channel.");
        }

        $channel = $request->getServer()->channels->get('id', $user->getPrivateChannel()->getChannelId());
        $request->getServer()->channels->delete($channel);
        $privateChannel = $user->getPrivateChannel();
        $user->setPrivateChannel(null);
        $this->getManager()->remove($privateChannel);

        $request->reply("Channel deleted.");
    }

    protected function addMemberToPrivateChannel(Request $request, array $matches)
    {
        $this->logger->info("Creating private channel");
        $repo = $this->getManager()->getRepository(User::class);

        /** @var User $user */
        $user = $repo->findOneByIdentifier($request->getAuthor()->id);
        if (empty($user)) {
            $user = new User();
            $user->setIdentifier($request->getAuthor()->id);
            $user->setServer($request->getDatabaseServer());
            $this->getManager()->persist($user);
            $this->getManager()->flush($user);
        }

        if (empty($user->getPrivateChannel())) {
            return $request->reply("You don't have a private voice channel.");
        }

        $channel = $request->getServer()->channels->get('id', $user->getPrivateChannel()->getChannelId());

        $this->logger->info("Creating mentioned perms");
        // Allow mentioned
        $promises = [];
        foreach ($request->getMentions() as $mention) {
            $perm   = $this->discord->factory(ChannelPermission::class, ['voice_connect' => true]);
            $member = UserHelper::getMember($mention, $request->getServer());
            $this->logger->info("Allowing: ".$member);
            $promises[] = $channel->setPermissions($member, $perm);
        }

        $promise = \React\Promise\all($promises);
        $promise->then(
            function () use ($request) {
                $request->reply("Permissions updated.");
            }
        );
    }

    protected function removeMemberFromPrivateChannel(Request $request, array $matches)
    {
        $this->logger->info("Creating private channel");
        $repo = $this->getManager()->getRepository(User::class);

        /** @var User $user */
        $user = $repo->findOneByIdentifier($request->getAuthor()->id);
        if (empty($user)) {
            $user = new User();
            $user->setIdentifier($request->getAuthor()->id);
            $user->setServer($request->getDatabaseServer());
            $this->getManager()->persist($user);
            $this->getManager()->flush($user);
        }

        if (empty($user->getPrivateChannel())) {
            return $request->reply("You don't have a private voice channel.");
        }

        /** @var Channel $channel */
        $channel = $request->getServer()->channels->get('id', $user->getPrivateChannel()->getChannelId());
        $general = $request->getServer()->channels->get('name', 'General');

        $this->logger->info("Removing mentioned perms");
        // Allow mentioned
        $promises = [];
        foreach ($request->getMentions() as $mention) {
            $perm   = $this->discord->factory(ChannelPermission::class, ['voice_connect' => false]);
            $member = UserHelper::getMember($mention, $request->getServer());
            $this->logger->info("Denying: ".$member);
            $promises[] = $channel->setPermissions($member, $perm);
            $general->moveMember($member);
        }

        $promise = \React\Promise\all($promises);
        $promise->then(
            function () use ($request) {
                $request->reply("Permissions updated.");
            }
        );
    }

    protected function createPrivateChannel(Request $request, array $matches)
    {
        $this->logger->info("Creating private channel");
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

        $this->logger->info("Creating channel");
        /** @var Channel $channel */
        $channel = $this->discord->factory(
            Channel::class,
            ['name' => trim($matches['name']), 'type' => Channel::TYPE_VOICE]
        );
        $request->getServer()->channels->save($channel)
            ->then(
                function (Channel $channel) use ($request, $matches, $user) {
                    $this->logger->info("Creating everyone perm");
                    $promises = [];


                    // Negate everyone
                    $perm = $this->discord->factory(ChannelPermission::class, ['voice_connect' => false]);
                    $everyone = $request->getServer()->roles->get('name', '@everyone');
                    $promises[] = $channel->setPermissions($everyone, $perm);

                    $this->logger->info("Creating author perm");
                    $perm = $this->discord->factory(ChannelPermission::class, ['voice_connect' => true]);
                    // Allow author
                    $promises[] = $channel->setPermissions(
                        UserHelper::getMember($request->getAuthor(), $request->getServer()),
                        $perm
                    );

                    $this->logger->info("Creating mentioned perms");
                    // Allow mentioned
                    foreach ($request->getMentions() as $mention) {
                        $perm   = $this->discord->factory(ChannelPermission::class, ['voice_connect' => true]);
                        $member = UserHelper::getMember($mention, $request->getServer());
                        $this->logger->info("Allowing: ".$member);
                        $promises[] = $channel->setPermissions($member, $perm);
                    }

                    $request->reply("Channel being created. Please wait.");
                    $promise = \React\Promise\all($promises);
                    $promise->then(function() use ($request, $user, $channel) {
                        $request->reply("Channel created.");
                        $privateChannel = new PrivateChannel();
                        $privateChannel->setChannelId($channel->id);
                        $privateChannel->setUser($user);
                        $privateChannel->setServer($request->getDatabaseServer());
                        $privateChannel->setInsertDate(new \DateTime());
                        $this->getManager()->persist($privateChannel);

                        $user->setPrivateChannel($privateChannel);
                        $this->getManager()->flush($user);
                    })->otherwise(
                        function ($error) use ($request, $channel) {
                            $request->reply("Failed to create channel.");
                            $request->getServer()->channels->delete($channel);
                            $this->logger->error($error);
                        }
                    );
                }
            );
    }
}
