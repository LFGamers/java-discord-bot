<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\BaseModule\Listener;

use Carbon\Carbon;
use Discord\Base\AppBundle\Event\BotEvent;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\User\Member;
use LFGamers\Discord\Helper\ConfigHelper;
use LFGamers\Discord\Model\Strike;
use LFGamers\Discord\ModerationModule\Punishment\Kick;
use LFGamers\Discord\ModerationModule\Punishment\Mute;
use LFGamers\Discord\ModerationModule\Punishment\PermanentBan;
use LFGamers\Discord\ModerationModule\Punishment\TemporaryBan;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ModLogListener Class
 */
class ModLogListener
{
    /**
     * @var Discord
     */
    private $discord;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @param Discord       $discord
     * @param ConfigHelper  $configHelper
     */
    public function __construct(Discord $discord, ConfigHelper $configHelper)
    {
        $this->discord      = $discord;
        $this->configHelper = $configHelper;
    }

    /**
     * @param BotEvent $event
     */
    public function onBotEvent(BotEvent $event)
    {
        /** @var Strike $strike */
        $strike = $event->getData()[0];
        $guild      = $this->discord->guilds->get('id', $strike->getServer()->getIdentifier());

        $channelId = $this->configHelper->get('modlog_channel.'.$guild->id);
        if (empty($channelId)) {
            return;
        }

        /** @var Channel $channel */
        $channel = $guild->channels->get('id', $channelId);
        $channel->sendMessage($this->getMessage($strike));
    }

    private function getMessage(Strike $strike) : array
    {
        $carbon    = Carbon::instance($strike->getInsertDate());
        $datetime  = $carbon->format('l jS \\of F Y \\at h:i:s A \\UTC');
        $moderator = $this->discord->members->get('id', $strike->getModerator()->getIdentifier());
        $action    = $this->getAction($strike->getAction());
        $user      = $this->discord->members->get('id', $strike->getUser()->getIdentifier());
        $duration  = $this->getDuration($strike->getDuration());

        return sprintf(
            "On %s, **%s** %s %s%s with reason: %s",
            $datetime,
            $moderator,
            $action,
            $user,
            $duration,
            $strike->getReason()
        );
    }

    private function getDuration(int $duration = null) {
        if (null === $duration) {
            return '';
        }

        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@" . $duration);

        return $dtF->diff($dtT)->format('%ad, %H:i');
    }

    private function getAction(string $action)
    {
        switch ($action) {
            case Mute::class:
                return 'muted';
            case Kick::class:
                return 'kicked';
            case TemporaryBan::class:
                return 'temporarily banned';
            case PermanentBan::class:
                return 'permanently banned';
            default:
                throw new \Exception("No action found.");
        }
    }
}
