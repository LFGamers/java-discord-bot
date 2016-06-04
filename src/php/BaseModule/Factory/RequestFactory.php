<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\BaseModule\Factory;

use Discord\Base\AppBundle\Discord;
use Discord\Base\AppBundle\Factory\RequestFactory as BaseRequestFactory;
use Discord\Parts\Channel\Message;
use LFGamers\Discord\Helper\AclHelper;
use LFGamers\Discord\Request;
use Monolog\Logger;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * RequestFactory Class
 */
class RequestFactory extends BaseRequestFactory
{
    /**
     * @var AclHelper
     */
    private $acl;

    /**
     * ServerManagerFactory constructor.
     *
     * @param Discord           $discord
     * @param Logger            $logger
     * @param \Twig_Environment $twig
     * @param int               $adminId
     * @param string            $prefix
     * @param bool              $interactive
     * @param AclHelper         $acl
     */
    public function __construct(
        Discord $discord,
        Logger $logger,
        \Twig_Environment $twig,
        $adminId,
        $prefix,
        $interactive,
        AclHelper $acl
    ) {
        parent::__construct($discord, $logger, $twig, $adminId, $prefix, $interactive);
        $this->acl = $acl;
    }

    /**
     * @param Message $message
     *
     * @return Request
     */
    public function create(Message $message)
    {
        $request = new Request(
            $this->discord,
            $this->logger,
            $this->twig,
            $this->adminId,
            $this->prefix,
            $message,
            $this->acl
        );

        if (!$this->interactive) {
            $request->setInteractive(false);
        }

        return $request;
    }
}
