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
     * @var PrivateChannel|null
     *
     * @ORM\OneToOne(targetEntity="PrivateChannel")
     * @ORM\JoinColumn(name="private_channel_id", referencedColumnName="id")
     */
    protected $privateChannel;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $karma = 0;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $lastSeen;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $lastSpoke;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $names;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->names     = [];
        $this->lastSeen  = new \DateTime();
        $this->lastSpoke = new \DateTime();
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
     * @return PrivateChannel|null
     */
    public function getPrivateChannel()
    {
        return $this->privateChannel;
    }

    /**
     * @param PrivateChannel|null $privateChannel
     *
     * @return User
     */
    public function setPrivateChannel(PrivateChannel $privateChannel = null) : User
    {
        $this->privateChannel = $privateChannel;

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

    /**
     * @return \DateTime
     */
    public function getLastSeen() : \DateTime
    {
        return $this->lastSeen;
    }

    /**
     * @param \DateTime $lastSeen
     *
     * @return User
     */
    public function setLastSeen(\DateTime $lastSeen) : User
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastSpoke() : \DateTime
    {
        return $this->lastSpoke;
    }

    /**
     * @param \DateTime $lastSpoke
     *
     * @return User
     */
    public function setLastSpoke(\DateTime $lastSpoke) : User
    {
        $this->lastSpoke = $lastSpoke;

        return $this;
    }

    public function addName($name) : User
    {
        $this->names[] = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getNames() : array
    {
        return $this->names;
    }

    /**
     * @param array $names
     *
     * @return User
     */
    public function setNames(array $names) : User
    {
        $this->names = $names;

        return $this;
    }
}
