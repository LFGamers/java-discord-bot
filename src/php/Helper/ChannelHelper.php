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

use Discord\Exceptions\PartRequestFailedException;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ChannelHelper Class
 */
class ChannelHelper
{
    public static function pruneChannel(Channel $channel, array $options = [])
    {
        /** @var Message[] $messages */
        $messages = array_merge($channel->messages->toArray(), self::getChannelHistory($channel));
        foreach ($messages as $message) {
            try {
                $message->delete();
            } catch (PartRequestFailedException $e) {
            }
        }
    }

    /**
     * @param Channel $channel
     * @param int     $limit
     * @param array   $messages
     *
     * @return array|Message[]
     * @throws \Exception
     */
    public static function getChannelHistory(Channel $channel, $limit = 0, array $messages = [])
    {
        $lastMessage = isset($messages[sizeof($messages) - 1]) ? $messages[sizeof($messages) - 1] : null;

        $msgs = $channel->getMessageHistory(['limit' => 100, 'before' => $lastMessage]);
        $messages = array_merge($messages, $msgs->toArray());
        if ($limit !== 0 && sizeof($messages) > $limit) {
            array_splice($messages, $limit);

            return $messages;
        }

        if (sizeof($messages) < 100) {
            return $messages;
        }

        return self::getChannelHistory($channel, $limit, $messages);
    }
}
