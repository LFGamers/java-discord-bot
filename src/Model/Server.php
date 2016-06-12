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

use Discord\Base\AppBundle\Model\Server as BaseServer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * Rule Class
 * @ORM\Entity
 * @ORM\Table(name="server")
 */
class Server extends BaseServer
{
    /**
     * @var ArrayCollection|User[]
     * @ORM\OneToMany(targetEntity="User", mappedBy="server")
     */
    protected $users;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $announcementsEnabled = false;

    /**
     * @var string
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $announcementsChannel;

    /**
     * @var ArrayCollection|array|Announcement[]
     * @ORM\OneToMany(targetEntity="Announcement", mappedBy="server")
     */
    protected $announcements;

    /**
     * @var int
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $lastAnnouncementMessage;

    /**
     * Server constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->users                = new ArrayCollection();
        $this->announcements        = new ArrayCollection();
        $this->announcementsEnabled = false;
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param array|User[] $users
     *
     * @return Server
     */
    public function setUsers(array $users) : Server
    {
        $this->users = new ArrayCollection($users);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAnnouncementsEnabled() : bool
    {
        return $this->announcementsEnabled;
    }

    /**
     * @param boolean $announcementsEnabled
     *
     * @return Server
     */
    public function setAnnouncementsEnabled(bool $announcementsEnabled) : Server
    {
        $this->announcementsEnabled = $announcementsEnabled;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAnnouncementsChannel()
    {
        return $this->announcementsChannel;
    }

    /**
     * @param string|null $announcementsChannel
     *
     * @return Server
     */
    public function setAnnouncementsChannel(string $announcementsChannel = null) : Server
    {
        $this->announcementsChannel = $announcementsChannel;

        return $this;
    }

    /**
     * @return ArrayCollection|array|Announcement[]
     */
    public function getAnnouncements()
    {
        return $this->announcements;
    }

    /**
     * @param array|Announcement[] $announcements
     *
     * @return Server
     */
    public function setAnnouncements(array $announcements) : Server
    {
        $this->announcements = new ArrayCollection($announcements);

        return $this;
    }

    /**
     * @param Announcement $announcement
     *
     * @return Server
     */
    public function addAnnouncement(Announcement $announcement) : Server
    {
        if (!$this->announcements->contains($announcement)) {
            $this->announcements->add($announcement);
        }

        return $this;
    }

    /**
     * @param Announcement $announcement
     *
     * @return Server
     */
    public function removeAnnouncement(Announcement $announcement) : Server
    {
        if ($this->announcements->contains($announcement)) {
            $this->announcements->removeElement($announcement);
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLastAnnouncementMessage()
    {
        return $this->lastAnnouncementMessage;
    }

    /**
     * @param int|null $lastAnnouncementMessage
     *
     * @return Server
     */
    public function setLastAnnouncementMessage(int $lastAnnouncementMessage = null) : Server
    {
        $this->lastAnnouncementMessage = $lastAnnouncementMessage;

        return $this;
    }
}
