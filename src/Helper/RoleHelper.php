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
use Discord\Parts\Permissions\RolePermission;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;
use DusanKasan\Knapsack\Collection;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * RoleHelper Class
 */
abstract class RoleHelper
{
    /**
     * Returns a collection of role objects for the give guild
     *
     * @param Guild $server
     *
     * @return Collection
     */
    public static function getServerRoles(Guild $server) : Collection
    {
        return new Collection($server->roles->all());
    }

    /**
     * Creates a role with the given options
     *
     *  name, hoist, mentionable, color, permissions, position
     *
     * @param array $options
     * @param Guild $server
     *
     * @return Role
     * @throws \Discord\Exceptions\PartRequestFailedException
     */
    public static function addRoleToServer(array $options, Guild $server) : Role
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['name', 'hoist', 'mentionable', 'color', 'permissions', 'position']);

        $resolver->setAllowedTypes('permissions', RolePermission::class);
        $resolver->setAllowedTypes('hoist', 'bool');
        $resolver->setAllowedTypes('mentionable', 'bool');
        $resolver->setAllowedTypes('color', 'int');
        $resolver->setAllowedTypes('position', 'int');

        $resolver->setDefault('permissions', new RolePermission());
        $resolver->setDefault('position', 0);

        $options = $resolver->resolve($options);

        $role = new Role();
        foreach ($options as $key => $value) {
            $role->{$key} = $value;
        }

        $role->guild_id = $server->id;
        $role->save();

        return $role;
    }

    /**
     * @param string|Role $role
     * @param Guild|null  $server
     *
     * @return bool
     * @throws \Discord\Exceptions\PartRequestFailedException
     * @throws \Exception
     */
    public static function deleteRoleFromServer($role, Guild $server = null) : bool
    {
        if (!($role instanceof Role)) {
            $role = static::getRoleByName($role, $server);
        }

        return $role->delete();
    }

    /**
     * @param string $name
     * @param Guild  $server
     *
     * @return Role
     * @throws \Exception
     */
    public static function getRoleByName($name, Guild $server) : Role
    {
        $role = $server->roles->get('name', $name);
        if (empty($role)) {
            throw new \Exception("Role not found: " . $name);
        }

        return $role;
    }

    /**
     * @param string|Role $role
     * @param array       $options
     * @param Guild|null  $server
     *
     * @return Role
     * @throws \Discord\Exceptions\PartRequestFailedException
     * @throws \Exception
     */
    public static function editRole($role, array $options, Guild $server = null) : Role
    {
        if (!($role instanceof Role)) {
            $role = static::getRoleByName($role, $server);
        }

        $resolver = new OptionsResolver();
        $resolver->setDefined(['name', 'hoist', 'mentionable', 'color', 'permissions', 'position']);

        $resolver->setAllowedTypes('permissions', RolePermission::class);
        $resolver->setAllowedTypes('hoist', 'bool');
        $resolver->setAllowedTypes('mentionable', 'bool');
        $resolver->setAllowedTypes('color', 'int');
        $resolver->setAllowedTypes('position', 'int');
        $options = $resolver->resolve($options);

        foreach ($options as $key => $value) {
            $role->{$key} = $value;
        }

        $role->save();

        return $role;
    }

    /**
     * @param User|Member $user
     * @param string|Role $role
     * @param Guild|null  $server
     *
     * @return bool
     * @throws \Discord\Exceptions\PartRequestFailedException
     * @throws \Exception
     */
    public static function addUserToRole($user, $role, Guild $server = null) : bool
    {
        if (!($role instanceof Role)) {
            $role = static::getRoleByName($role, $server);
        }

        if (!($user instanceof Member)) {
            $user = UserHelper::getMember($user, $server);
        }

        $user->addRole($role);

        return $user->save();
    }

    /**
     * @param User|Member $user
     * @param string|Role $role
     * @param Guild|null  $server
     *
     * @return bool
     * @throws \Discord\Exceptions\PartRequestFailedException
     * @throws \Exception
     */
    public static function removeUserFromRole($user, $role, Guild $server = null) : bool
    {
        if (!($role instanceof Role)) {
            $role = static::getRoleByName($role, $server);
        }

        if (!($user instanceof Member)) {
            $user = UserHelper::getMember($user, $server);
        }

        $user->removeRole($role);

        return $user->save();
    }

    /**
     * @param User|Member $user
     * @param Guild|null  $server
     *
     * @return Collection
     */
    public static function getUserRoles($user, Guild $server = null) : Collection
    {
        if (!($user instanceof Member)) {
            $user = UserHelper::getMember($user, $server);
        }

        return new Collection($user->roles->all());
    }

    /**
     * @param User|Member $user
     * @param Role|string $role
     * @param Guild|null  $server
     *
     * @return bool
     * @throws \Exception
     */
    public static function userHasRole($user, $role, Guild $server = null) : bool
    {
        if (!($user instanceof Member)) {
            $user = UserHelper::getMember($user, $server);
        }

        if (!($role instanceof Role)) {
            $role = static::getRoleByName($role, $server);
        }

        return !empty($user->roles->get('id', $role->id));
    }
}
