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

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * User Class
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
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
     * @var Server
     *
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="users")
     * @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     */
    protected $server;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $karma = 0;

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
     * @return User
     */
    public function setId(int $id) : User
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
     * @return User
     */
    public function setIdentifier(int $identifier) : User
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return Server
     */
    public function getServer() : Server
    {
        return $this->server;
    }

    /**
     * @param Server $server
     *
     * @return User
     */
    public function setServer(Server $server) : User
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return int
     */
    public function getKarma() : int
    {
        return $this->karma;
    }

    /**
     * @param int $karma
     *
     * @return User
     */
    public function setKarma(int $karma) : User
    {
        $this->karma = $karma;

        return $this;
    }
}
