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

use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use React\Promise\Promise;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ChannelHelper Class
 */
class ChannelHelper
{
    /**
     * @param Channel $channel
     * @param int     $limit
     * @param array   $messages
     *
     * @return FulfilledPromise|Promise
     * @throws \Exception
     */
    public static function getChannelHistory(Channel $channel, $limit = 0, array $messages = [])
    {
        $deferred = new Deferred();

        if ($limit !== 0 && sizeof($messages) > $limit) {
            array_splice($messages, $limit);

            return new FulfilledPromise($messages);
        }

        $lastMessage = isset($messages[sizeof($messages) - 1]) ? $messages[sizeof($messages) - 1] : null;
        $options     = ['limit' => 100];
        if ($lastMessage !== null) {
            $options['before'] = $lastMessage;
        }

        $channel->getMessageHistory($options)
            ->then(
                function (Collection $msgs) use ($channel, $limit, $messages, $deferred) {
                    $messages = array_merge($messages, $msgs->toArray());

                    if ($msgs->count() < 100) {
                        return $deferred->resolve($messages);
                    }

                    static::getChannelHistory($channel, $limit, $messages)
                        ->then(
                            function ($messages) use ($deferred) {
                                $deferred->resolve($messages);
                            }
                        )->otherwise(
                            function () use ($messages, $deferred) {
                                $deferred->resolve($messages);
                            }
                        );
                }
            )
            ->otherwise(
                function ($error) use ($deferred, $messages) {
                    $deferred->resolve($messages);
                }
            );

        return $deferred->promise();
    }

    public static function deleteMessages(Channel $channel, array $messages)
    {
        $deferred = new Deferred();

        $msgs = array_splice($messages, 100);

        $channel->deleteMessages($messages)
            ->then(
                function () use ($channel, $msgs, $deferred) {
                    if (sizeof($msgs) <= 0) {
                        return $deferred->resolve();
                    }

                    sleep(1);
                    static::deleteMessages($channel, $msgs)
                        ->then(
                            function () use ($deferred) {
                                $deferred->resolve();
                            }
                        )
                        ->otherwise(
                            function ($error) use ($deferred) {
                                $deferred->reject($error);
                            }
                        );
                }
            )
            ->otherwise(
                function ($error) use ($deferred) {
                    $deferred->reject($error);
                }
            );

        return $deferred->promise();
    }
}
