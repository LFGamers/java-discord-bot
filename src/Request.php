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

use Discord\Base\AbstractBotCommand as BaseBotCommand;
use Discord\Base\Request as BaseRequest;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use LFGamers\Discord\Exception\MemberNotFoundException;
use LFGamers\Discord\Helper\AclHelper;
use Monolog\Logger;
use React\EventLoop\LoopInterface;
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

    public function processCommand(BaseBotCommand $command)
    {
        /** @var LoopInterface $loop */
        $loop = $this->getDiscord()->loop;
        $loop->nextTick(
            function () use ($command) {
                try {
                    parent::processCommand($command);
                } catch (MemberNotFoundException $e) {
                    $this->getLogger()->error($e->getMessage());
                }
            }
        );
    }

    /**
     * @return array|User[]
     */
    public function getMentions()
    {
        $mentions = [];
        foreach ($this->getMessage()->mentions as $mention) {
            $mentions[] = $this->getDiscord()->users->get('id', $mention->id);
        }

        return $mentions;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        if (parent::isAdmin()) {
            return true;
        }

        // Owner
        if ($this->acl->userHasRole($this->getGuildAuthor(), 206020688098754562, $this->getServer())) {
            return true;
        }

        // Community Adviser
        if ($this->acl->userHasRole($this->getGuildAuthor(), 206021042454396929, $this->getServer())) {
            return true;
        }

        return false;
    }
}
