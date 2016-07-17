<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\ModerationModule\Punishment;

use Discord\Discord;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\User;
use LFGamers\Discord\Helper\UserHelper;
use LFGamers\Discord\Model\Strike;
use React\Promise\FulfilledPromise;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * AbstractPunishment Class
 */
abstract class AbstractPunishment
{
    /**
     * @var Discord
     */
    protected $discord;

    /**
     * AbstractPunishment constructor.
     *
     * @param Discord $discord
     */
    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    /**
     * @param Strike $strike
     *
     * @return FulfilledPromise|Promise|PromiseInterface
     */
    abstract public function perform(Strike $strike);

    /**
     * @param Strike $strike
     *
     * @return FulfilledPromise|Promise|PromiseInterface
     */
    abstract public function resolve(Strike $strike);

    /**
     * @param Strike        $strike
     * @param callable|null $callback
     * @param callable|null $errorCallback
     *
     * @return FulfilledPromise|Promise|PromiseInterface
     */
    protected function getMember(Strike $strike, callable $callback = null, callable $errorCallback = null)
    {
        $promise = UserHelper::getMember($this->getUser($strike), $this->getGuild($strike));

        if ($callback !== null) {
            $promise->then($promise);
        }

        if ($errorCallback !== null) {
            $promise->otherwise($errorCallback);
        }

        return $promise;
    }

    /**
     * @param Strike $strike
     *
     * @return User
     */
    protected function getUser(Strike $strike) : User
    {
        return $this->discord->users->get('id', $strike->getUser()->getIdentifier());
    }

    /**
     * @param Strike $strike
     *
     * @return Guild
     */
    protected function getGuild(Strike $strike) : Guild
    {
        return $this->discord->guild->get('id', $strike->getGuild()->getIdentifier());
    }
}
