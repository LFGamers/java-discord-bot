<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord;

use Discord\Base\AbstractBotCommand as BaseAbstractBotCommand;
use Discord\Parts\Guild\Role;
use Discord\Parts\User\Member;
use Discord\Parts\User\User as UserPart;
use LFGamers\Discord\Helper\AclHelper;
use LFGamers\Discord\Helper\ConfigHelper;
use LFGamers\Discord\Model\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * AbstractBotCommand Class
 */
abstract class AbstractBotCommand extends BaseAbstractBotCommand
{
    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * @var AclHelper
     */
    protected $acl;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->config = $container->get('helper.config');
        $this->acl    = $container->get('helper.acl');
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->config->get($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setConfig($key, $value)
    {
        return $this->config->set($key, $value);
    }

    /**
     * @param Member|Role $resource
     * @param string      $permission
     *
     * @return bool
     * @throws \Exception
     */
    public function isAllowed($resource, string $permission) : bool
    {
        if ($resource instanceof Member) {
            return $this->acl->isAllowed($resource, $permission);
        }

        if ($resource instanceof Role) {
            return $this->acl->isRoleAllowed($resource, $permission);
        }

        throw new \Exception(sprintf("first argument must be an instance of %s or %s.", Member::class, Role::class));
    }

    /**
     * @param string|int $identifier
     *
     * @return UserPart
     */
    protected function getClientUser($identifier) : UserPart
    {
        return $this->discord->users->get('id', $identifier);
    }

    /**
     * @param string|int $identifier
     *
     * @return User
     */
    protected function getDatabaseUser($identifier) : User
    {
        $user = $this->getManager()->getRepository(User::class)->findOneByIdentifier($identifier);
        if (empty($user)) {
            $user = new User();
            $user->setIdentifier($identifier);
            $this->getManager()->persist($user);
            $this->getManager()->flush($user);
        }

        return $user;
    }
}
