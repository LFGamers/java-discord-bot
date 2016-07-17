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

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * Strike Class
 * @ORM\Entity
 * @ORM\Table(name="strike")
 */
class Strike
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="strikes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="strikes")
     * @ORM\JoinColumn(name="moderator_id", referencedColumnName="id")
     */
    protected $moderator;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $reason;

    /**
     * @var Server
     *
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="punishments")
     * @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     */
    protected $server;

    /**
     * @var string
     * @ORM\Column(type="string", length=256)
     */
    protected $action;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $duration;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $insertDate;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $resolved = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Strike
     */
    public function setId(int $id) : Strike
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
     * @return Strike
     */
    public function setUser(User $user) : Strike
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getModerator() : User
    {
        return $this->moderator;
    }

    /**
     * @param User $moderator
     *
     * @return Strike
     */
    public function setModerator(User $moderator) : Strike
    {
        $this->moderator = $moderator;

        return $this;
    }

    /**
     * @return string
     */
    public function getReason() : string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     *
     * @return Strike
     */
    public function setReason(string $reason) : Strike
    {
        $this->reason = $reason;

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
     * @return Strike
     */
    public function setServer(Server $server) : Strike
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction() : string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return Strike
     */
    public function setAction(string $action) : Strike
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration() : int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     *
     * @return Strike
     */
    public function setDuration(int $duration) : Strike
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getInsertDate() : DateTime
    {
        return $this->insertDate;
    }

    /**
     * @param DateTime $insertDate
     *
     * @return Strike
     */
    public function setInsertDate(DateTime $insertDate) : Strike
    {
        $this->insertDate = $insertDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isResolved() : bool
    {
        return $this->resolved;
    }

    /**
     * @param boolean $resolved
     *
     * @return Strike
     */
    public function setResolved(bool $resolved) : Strike
    {
        $this->resolved = $resolved;

        return $this;
    }
}
