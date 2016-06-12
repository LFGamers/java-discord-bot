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
 * Announcement Class
 * @ORM\Entity
 * @ORM\Table(name="announcement")
 */
class Announcement
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Server
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="announcements")
     */
    protected $server;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @var int
     * @ORM\Column(type="integer", length=5)
     */
    protected $priority = 0;

    /**
     * @var int
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $author;

    public function __construct()
    {
        $this->priority = 0;
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
     * @return Announcement
     */
    public function setId(int $id) : Announcement
    {
        $this->id = $id;

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
     * @return Announcement
     */
    public function setServer(Server $server) : Announcement
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Announcement
     */
    public function setTitle(string $title) : Announcement
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent() : string
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return Announcement
     */
    public function setContent(string $content) : Announcement
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority() : int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return Announcement
     */
    public function setPriority(int $priority) : Announcement
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getAuthor() : int
    {
        return $this->author;
    }

    /**
     * @param int $author
     *
     * @return Announcement
     */
    public function setAuthor(int $author) : Announcement
    {
        $this->author = $author;

        return $this;
    }
}
