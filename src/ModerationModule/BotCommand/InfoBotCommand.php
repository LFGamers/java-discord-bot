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

use Carbon\Carbon;
use Discord\Base\AppBundle\Event\ServerEvent;
use Discord\Base\Request;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Guild\Role;
use Discord\Parts\User\Member;
use Doctrine\ORM\ORMException;
use LFGamers\Discord\AbstractBotCommand;
use LFGamers\Discord\Helper\RoleHelper;
use LFGamers\Discord\Helper\UserHelper;
use LFGamers\Discord\Model\Punishment;
use LFGamers\Discord\Model\Strike;
use LFGamers\Discord\Model\User;
use LFGamers\Discord\ModerationModule\Punishment\AbstractPunishment;
use LFGamers\Discord\ModerationModule\Punishment\Mute;
use LFGamers\Discord\ModerationModule\Punishment\PermanentBan;
use LFGamers\Discord\ModerationModule\Punishment\TemporaryBan;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * InfoBotCommand Class
 */
class InfoBotCommand extends AbstractBotCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this
            ->setName('info')
            ->setDescription('Retrieves various informations about the servers or members.')
            ->setHelp(
                <<<EOF
Use the following to get information:

`info` to get server information
`info #channel` to get information on a channel
`info @user` to get information on a user
EOF
            );
    }

    /**
     * @return void
     */
    public function setHandlers()
    {
        $this->responds('/^info$/', [$this, 'getServerInfo']);
        $this->responds('/^info <#(?<channel>\d+)>$/', [$this, 'getChannelInfo']);
        $this->responds('/^info <@!?(?<user>\d+)>$/', [$this, 'getUserInfo']);
        $this->responds('/^info (?<name>.*)$/', [$this, 'getUserInfoByName']);
    }

    protected function getUserInfoByName(Request $request, array $matches)
    {
        $name = $matches['name'];
        $user = null;

        if (strpos($name, '#') !== false) {
            $temp          = explode('#', $name);
            $name          = $temp[0];
            $discriminator = $temp[1];

            $user = $request->getServer()->members->filter(
                function (Member $member) use ($name, $discriminator) {
                    return $member->username === $name && $member->discriminator === $discriminator;
                }
            )->get(0);
        }

        if (empty($user)) {
            $name = $matches['name'];

            /** @var Collection $users */
            $users = $request->getServer()->members->filter(
                function (Member $member) use ($name) {
                    if ($member->username === $name) {
                        return $member;
                    }
                }
            );

            if ($users->count() > 1) {
                return $request->reply("There are multiple users with that name.");
            }

            if ($users->count() < 1) {
                return $request->reply("There are no users with that name.");
            }

            $user = $users->get(0);
        }

        return $this->getUserInfo($request, ['user' => $user->id]);
    }

    protected function getServerInfo(Request $request)
    {
        /**
         * @var Guild      $server
         * @var Collection $channels
         * @var Collection $roles
         */
        $server        = $request->getServer();
        $channels      = $server->channels;
        $roles         = $server->roles;
        $created       = Carbon::createFromTimestamp((($server->id / 4194304) + 1420070400000) / 1000);
        $textChannels  = $channels->filter(
            function (Channel $channel) {
                return $channel->type === Channel::TYPE_TEXT;
            }
        );
        $voiceChannels = $channels->filter(
            function (Channel $channel) {
                return $channel->type === Channel::TYPE_VOICE;
            }
        );
        $roles         = $roles->sortByDesc('position')->map(
            function (Role $role) {
                return str_replace('@', '@​', $role->name);
            }
        );

        try {
            $request->reply(
                $request->renderTemplate(
                    '@ModerationModule/Info/server.md.twig',
                    [
                        'server'        => $server,
                        'owner'         => $server->owner,
                        'icon'          => $server->icon,
                        'created'       => $created,
                        'textChannels'  => $textChannels,
                        'voiceChannels' => $voiceChannels,
                        'roles'         => $roles
                    ]
                )
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    protected function getChannelInfo(Request $request, array $matches)
    {
        /**
         * @var Channel $channel
         */
        $channel = $request->getServer()->channels->get('id', $matches['channel']);
        $created = Carbon::createFromTimestamp((($channel->id / 4194304) + 1420070400000) / 1000);

        try {
            $request->reply(
                $request->renderTemplate(
                    '@ModerationModule/Info/channel.md.twig',
                    [
                        'channel' => $channel,
                        'created' => $created
                    ]
                )
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    protected function getUserInfo(Request $request, array $matches)
    {
        /**
         * @var Member $member
         */
        $member  = $request->getServer()->members->get('id', $matches['user']);
        $created = Carbon::createFromTimestamp((($member->id / 4194304) + 1420070400000) / 1000);
        $joined  = $member->joined_at;
        $roles   = $member->roles->sortByDesc('position')->map(
            function (Role $role) {
                return str_replace('@', '@​', $role->name);
            }
        );

        /** @var User $dbUser */
        $dbUser    = $this->getManager()->getRepository(User::class)->findOneBy(['identifier' => $member->id]);
        $lastSeen  = Carbon::instance($dbUser->getLastSeen());
        $lastSpoke = Carbon::instance($dbUser->getLastSpoke());

        try {
            $request->reply(
                $request->renderTemplate(
                    '@ModerationModule/Info/user.md.twig',
                    [
                        'member'    => $member,
                        'dbUser'    => $dbUser,
                        'created'   => $created,
                        'joined'    => $joined,
                        'lastSeen'  => $lastSeen,
                        'lastSpoke' => $lastSpoke,
                        'roles'     => $roles
                    ]
                )
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
