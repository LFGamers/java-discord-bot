<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\Helper;

use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\Member;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use LFGamers\Discord\Model\Config;
use LFGamers\Discord\Model\Strike;
use LFGamers\Discord\ModerationModule\Punishment\Kick;
use LFGamers\Discord\ModerationModule\Punishment\Mute;
use LFGamers\Discord\ModerationModule\Punishment\PermanentBan;
use LFGamers\Discord\ModerationModule\Punishment\TemporaryBan;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ModLogHelper Class
 */
class ModLogHelper
{
    /**
     * @param Guild  $guild
     * @param string $message
     *
     * @return bool
     */
    public static function postMessage(Guild $guild, string $message) : bool
    {
        /** @var Channel $channel */
        $channel = $guild->channels->get('name', 'mod-log');
        if (empty($channel)) {
            return false;
        }

        $channel->sendMessage($message);

        return true;
    }

    /**
     * @param Strike $strike
     * @param Member $member
     *
     * @return bool
     */
    public static function postStrike(Strike $strike, Member $member)
    {
        $message = static::getMessage($member->guild, $strike);

        return static::postMessage($member->guild, $message);
    }

    /**
     * @param Guild  $guild
     * @param Strike $strike
     *
     * @return string
     */
    private static function getMessage(Guild $guild, Strike $strike) : string
    {
        $carbon    = Carbon::instance($strike->getInsertDate());
        $datetime  = $carbon->format('l jS \\o\\f F Y \\a\\t h:i:s A \\U\\T\\C');
        $moderator = $guild->members->get('id', $strike->getModerator()->getIdentifier());
        $action    = static::getAction($strike->getAction());
        $user      = $guild->members->get('id', $strike->getUser()->getIdentifier());
        $duration  = static::getDuration($strike->getDuration());

        $message = sprintf(
            "On %s, **%s** %s %s%s with the reason:\n\n```\n%s\n```",
            $datetime,
            $moderator->username,
            $action,
            $user->username,
            $duration,
            $strike->getReason()
        );

        var_dump($message);

        return $message;
    }

    /**
     * @param int|null $duration
     *
     * @return string
     */
    private static function getDuration(int $duration = null)
    {
        if (null === $duration) {
            return '';
        }

        $zero   = new \DateTime('@0');
        $offset = clone $zero;
        $offset->modify("+$duration seconds");
        $diff = $zero->diff($offset);

        return 'for '.sprintf("%02d:%02d:%02d", $diff->days * 24 + $diff->h, $diff->i, $diff->s);
    }

    /**
     * @param string $action
     *
     * @return string
     * @throws \Exception
     */
    private static function getAction(string $action)
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
