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
 * @ORM\Table(name="private_channel")
 */
class PrivateChannel
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @var Server
     *
     * @ORM\OneToOne(targetEntity="Server")
     */
    protected $server;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $insertDate;

    /**
     * @var int
     * @ORM\Column(type="bigint")
     */
    protected $channelId;

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
     * @return PrivateChannel
     */
    public function setId(int $id) : PrivateChannel
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return PrivateChannel
     */
    public function setUser(User $user) : PrivateChannel
    {
        $this->user = $user;

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
     * @return PrivateChannel
     */
    public function setServer(Server $server) : PrivateChannel
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return int
     */
    public function getChannelId() : int
    {
        return $this->channelId;
    }

    /**
     * @param int $channelId
     *
     * @return PrivateChannel
     */
    public function setChannelId(int $channelId) : PrivateChannel
    {
        $this->channelId = $channelId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getInsertDate() : \DateTime
    {
        return $this->insertDate;
    }

    /**
     * @param \DateTime $insertDate
     *
     * @return PrivateChannel
     */
    public function setInsertDate(\DateTime $insertDate) : PrivateChannel
    {
        $this->insertDate = $insertDate;

        return $this;
    }
}
