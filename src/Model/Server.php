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
     * Server constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->users = new ArrayCollection();
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
}
