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

use Discord\Base\AppBundle\Discord;
use Discord\Base\Request as BaseRequest;
use Discord\Parts\Channel\Message;
use LFGamers\Discord\Helper\AclHelper;
use LFGamers\Discord\Helper\UserHelper;
use Monolog\Logger;
use Twig_Environment;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * Request Class
 */
class Request extends BaseRequest
{
    /**
     * @var AclHelper
     */
    private $acl;

    /**
     * Request constructor.
     *
     * @param Discord          $discord
     * @param Logger           $logger
     * @param Twig_Environment $twig
     * @param string           $adminId
     * @param string           $prefix
     * @param Message          $message
     * @param AclHelper        $acl
     */
    public function __construct(
        Discord $discord,
        Logger $logger,
        Twig_Environment $twig,
        $adminId,
        $prefix,
        Message $message,
        AclHelper $acl
    ) {
        parent::__construct($discord, $logger, $twig, $adminId, $prefix, $message);
        $this->acl = $acl;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        $member = UserHelper::getMember($this->getAuthor(), $this->getServer());

        return parent::isAdmin()
        || $this->acl->userHasRole($member, 'Owners')
        || $this->acl->userHasRole($member, 'Community Adviser');
    }

}