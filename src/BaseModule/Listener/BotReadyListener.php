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
use Discord\Parts\Guild\Role;
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
     * @var Discord
     */
    private $discord;

    /**
     * BotReadyListener constructor.
     *
     * @param AclHelper $acl
     * @param Discord   $discord
     */
    public function __construct(AclHelper $acl, Discord $discord)
    {
        $this->acl = $acl;
        $this->discord = $discord;
    }

    /**
     * @param BotEvent $event
     */
    public function onBotReady(BotEvent $event)
    {
        // Global Perms
        $this->ensurePermission('Owners', '*');
        $this->ensurePermission('Community Adviser', '*');

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

        $this->ensurePermission('Community Moderator', 'moderation.*');
        $this->ensurePermission('Chief', 'moderation.*');
        $this->ensurePermission('Senior', 'moderation.*');
        $this->ensurePermission('Junior', 'moderation.kick');
        $this->ensurePermission('Junior', 'moderation.mute');

        // Strikes
        $this->ensurePermission('Staff', 'moderation.strike.view');
        $this->ensurePermission('Staff', 'moderation.strike.give');

        // Purge
        $this->ensurePermission('Staff', 'moderation.purge');
        $this->ensurePermission('Owners', 'moderation.purge.all');

        // Mod Log Perms
        $this->ensurePermission('Staff', 'modlog.*');

        // Fun Perms
        $this->ensurePermission('Staff', 'karma.staff');
        $this->ensurePermission('Staff', 'tag.set');
    }

    /**
     * @param string $role
     * @param string $permission
     * @param bool   $allowed
     */
    protected function ensurePermission(string $role, string $permission, $allowed = true)
    {
        foreach ($this->discord->guilds as $server) {
            $this->acl->ensurePermission($this->getRole($server, $role), $permission, $allowed);
        }
    }

    /**
     * @param Guild  $guild
     * @param string $name
     *
     * @return \Discord\Parts\Guild\Role
     */
    protected function getRole(Guild $guild, string $name) : Role
    {
        return RoleHelper::getRoleByName($name, $guild);
    }
}
