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
}
