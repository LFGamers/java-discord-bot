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

use Discord\Base\AppBundle\Event\BotEvent;
use Discord\Discord;
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

        $this->server = $discord->guilds->get('id', 108439985920704512);
    }

    /**
     * @param BotEvent $event
     */
    public function onBotReady(BotEvent $event)
    {
        $this->wipePermissions();
        
        // Global Perms
        $this->grantPermission('Owners', '*');
        $this->grantPermission('Community Adviser', '*');

        /*
         * Moderator Perms
         *
         * Perma Ban: moderation.ban.permanent
         * Temp Ban:  moderation.ban.temporary
         * Soft Ban:  moderation.ban.soft
         * Kick:      moderation.kick
         * Mute:      moderation.mute
         * Purge:     moderation.purge
         */

        $this->grantPermission('Community Moderator', 'moderation.*');
        $this->grantPermission('Chief', 'moderation.*');
        $this->grantPermission('Senior', 'moderation.*');
        $this->grantPermission('Junior', 'moderation.kick');
        $this->grantPermission('Junior', 'moderation.mute');

        // Strikes
        $this->grantPermission('Staff', 'moderation.strike.view');
        $this->grantPermission('Staff', 'moderation.strike.give');

        // Mod Log Perms
        $this->grantPermission('Staff', 'modlog.*');

        // Fun Perms
        $this->grantPermission('Staff', 'karma.staff');
        $this->grantPermission('Staff', 'tag.set');
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

    private function wipePermissions()
    {
        $this->acl->wipeServerPermissions($this->server);
    }
}
