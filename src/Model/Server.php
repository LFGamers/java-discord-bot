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

use Discord\Base\AppBundle\Model\BaseServer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * Rule Class
 * @ORM\Entity
 * @ORM\Table(name="server")
 */
class Server
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var BaseServer
     * @ORM\ManyToOne(targetEntity="Discord\Base\AppBundle\Model\BaseServer")
     * @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     */
    protected $server;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ruleChannel;

    /**
     * @var ArrayCollection|Rule[]
     * @ORM\OneToMany(targetEntity="LFGamers\Discord\Model\Rule", mappedBy="server")
     */
    protected $rules;

    /**
     * Server constructor.
     */
    public function __construct()
    {
        $this->rules = new ArrayCollection();
    }

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
     * @return Server
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return BaseServer
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param BaseServer $server
     *
     * @return Server
     */
    public function setServer(BaseServer $server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return string
     */
    public function getRuleChannel()
    {
        return $this->ruleChannel;
    }

    /**
     * @param string $ruleChannel
     *
     * @return Server
     */
    public function setRuleChannel($ruleChannel)
    {
        $this->ruleChannel = $ruleChannel;

        return $this;
    }

    /**
     * @return ArrayCollection|Rule[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param ArrayCollection|Rule[] $rules
     *
     * @return Server
     */
    public function setRules(array $rules)
    {
        $this->rules = new ArrayCollection($rules);

        return $this;
    }
}
