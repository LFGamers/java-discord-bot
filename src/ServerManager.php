<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord;

use Discord\Base\AppBundle\Manager\ServerManager as BaseServerManager;
use LFGamers\Discord\Model\Server;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ServerManager Class
 */
class ServerManager extends BaseServerManager
{
    protected function fetchDatabaseServer()
    {
        $baseServer = parent::fetchDatabaseServer();

        $server = $this->getRepository('LFG:Server')
            ->findOneBy(['server' => $baseServer]);

        if (empty($server)) {
            $server = new Server();
            $server->setServer($baseServer);

            $this->getManager()->persist($server);
            $this->getManager()->flush($server);
        }

        return $baseServer;
    }

}
