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
 * @ORM\Table(name="karma_log")
 */
class KarmaLog
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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id", nullable=true, unique=false)
     */
    protected $recipient;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=true, unique=false)
     */
    protected $sender;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $insertDate;

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
     * @return KarmaLog
     */
    public function setId(int $id) : KarmaLog
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param User $recipient
     *
     * @return KarmaLog
     */
    public function setRecipient(User $recipient) : KarmaLog
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     *
     * @return KarmaLog
     */
    public function setSender(User $sender) : KarmaLog
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSender() : bool
    {
        return $this->getSender() !== null;
    }

    /**
     * @return bool
     */
    public function hasRecipient() : bool
    {
        return $this->getSender() !== null;
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
     * @return KarmaLog
     */
    public function setInsertDate(\DateTime $insertDate) : KarmaLog
    {
        $this->insertDate = $insertDate;

        return $this;
    }
}
