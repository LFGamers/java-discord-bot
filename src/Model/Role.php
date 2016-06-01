<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * User Class
 * @ORM\Entity
 * @ORM\Table(name="role")
 */
class Role
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="bigint")
     */
    protected $identifier;

    /**
     * @var ArrayCollection|Permission[]
     *
     * @ORM\OneToMany(targetEntity="Permission", mappedBy="role")
     */
    protected $permissions;

    /**
     * Role constructor.
     */
    public function __construct()
    {
        $this->permissions = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Role
     */
    public function setId(int $id) : Role
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getIdentifier() : int
    {
        return $this->identifier;
    }

    /**
     * @param int $identifier
     *
     * @return Role
     */
    public function setIdentifier(int $identifier) : Role
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return ArrayCollection|Permission[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     *
     * @return Role
     */
    public function setPermissions(array $permissions) : Role
    {
        $this->permissions = new ArrayCollection($permissions);

        return $this;
    }

    /**
     * @param Permission $permission
     *
     * @return Role
     */
    public function addPermission(Permission $permission) : Role
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }

        return $this;
    }
}
