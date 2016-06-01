<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\BaseModule\Listener;

use Discord\Base\AppBundle\Discord;
use Discord\Base\AppBundle\Event\BotEvent;
use Discord\Parts\Guild\Guild;
use LFGamers\Discord\Helper\AclHelper;
use LFGamers\Discord\Helper\RoleHelper;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * BotReadyListener Class
 */
class BotReadyListener
{
    /**
     * @var AclHelper
     */
    private $acl;

    /**
     * @var Guild
     */
    protected $server;

    /**
     * BotReadyListener constructor.
     *
     * @param AclHelper $acl
     * @param Discord   $discord
     */
    public function __construct(AclHelper $acl, Discord $discord)
    {
        $this->acl = $acl;

        $this->server = $discord->client->guilds->get('id', 108439985920704512);
    }

    /**
     * @param BotEvent $event
     */
    public function onBotReady(BotEvent $event)
    {
        // Global Perms
        $this->grantPermission('Owners', '*');
        $this->grantPermission('Community Adviser', '*');

        // Moderator Perms

        // Ban
        $this->grantPermission('Community Moderator', 'moderation.ban.permanent');
        $this->grantPermission('Chief', 'moderation.ban.permanent');
        $this->grantPermission('Senior', 'moderation.ban.permanent');

        // Temp Ban
        $this->grantPermission('Community Moderator', 'moderation.ban.temporary');
        $this->grantPermission('Chief', 'moderation.ban.temporary');
        $this->grantPermission('Senior', 'moderation.ban.temporary');

        // Soft Ban
        $this->grantPermission('Community Moderator', 'moderation.ban.soft');
        $this->grantPermission('Chief', 'moderation.ban.soft');
        $this->grantPermission('Senior', 'moderation.ban.soft');

        // Kick
        $this->grantPermission('Community Moderator', 'moderation.kick');
        $this->grantPermission('Chief', 'moderation.kick');
        $this->grantPermission('Senior', 'moderation.kick');
        $this->grantPermission('Junior', 'moderation.kick');

        // Mute
        $this->grantPermission('Community Moderator', 'moderation.mute');
        $this->grantPermission('Chief', 'moderation.mute');
        $this->grantPermission('Senior', 'moderation.mute');
        $this->grantPermission('Junior', 'moderation.mute');

        // Purge
        $this->grantPermission('Community Moderator', 'moderation.purge');
        $this->grantPermission('Chief', 'moderation.purge');
        $this->grantPermission('Senior', 'moderation.purge');
    }

    /**
     * @param string $role
     * @param string $permission
     * @param bool   $allowed
     */
    protected function grantPermission(string $role, string $permission, $allowed = true)
    {
        $this->acl->grantPermission($this->getRole($role), $permission, $allowed);
    }

    /**
     * @param string $name
     *
     * @return \Discord\Parts\Guild\Role
     * @throws \Exception
     */
    protected function getRole(string $name)
    {
        return RoleHelper::getRoleByName($name, $this->server);
    }
}
