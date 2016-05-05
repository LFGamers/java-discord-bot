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
 * Rule Class
 * @ORM\Entity
 * @ORM\Table(name="rule")
 */
class Rule
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
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="rules")
     * @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     */
    protected $server;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @var string
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    protected $text;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $subtext;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     */
    protected $strikes;

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
     * @return Rule
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param Server $server
     *
     * @return Rule
     */
    public function setServer(Server $server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return Rule
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return Rule
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubtext()
    {
        return $this->subtext;
    }

    /**
     * @param string $subtext
     *
     * @return Rule
     */
    public function setSubtext($subtext)
    {
        $this->subtext = $subtext;

        return $this;
    }

    /**
     * @return float
     */
    public function getStrikes()
    {
        return $this->strikes;
    }

    /**
     * @param float $strikes
     *
     * @return Rule
     */
    public function setStrikes($strikes)
    {
        $this->strikes = $strikes;

        return $this;
    }
}
