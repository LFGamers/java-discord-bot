<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\BaseModule\Service;

use Carbon\Carbon;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\Member;
use Doctrine\Common\Persistence\ObjectManager;
use LFGamers\Discord\Helper\ConfigHelper;
use LFGamers\Discord\Helper\UserHelper;
use LFGamers\Discord\Model\Announcement;
use LFGamers\Discord\Model\Config;
use LFGamers\Discord\Model\Punishment;
use LFGamers\Discord\Model\Server;
use LFGamers\Discord\Model\Strike;
use LFGamers\Discord\ModerationModule\Punishment\AbstractPunishment;
use LFGamers\Discord\ServerManager;
use Monolog\Logger;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * PrivateChannelChecker Class
 */
class PunishmentChecker
{
    /**
     * @var Discord
     */
    private $discord;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * PrivateChannelChecker constructor.
     *
     * @param Discord       $discord
     * @param ObjectManager $manager
     * @param Logger        $logger
     */
    public function __construct(Discord $discord, ObjectManager $manager, Logger $logger)
    {
        $this->discord = $discord;
        $this->manager = $manager;
        $this->logger  = $logger;

        $this->discord->loop->addPeriodicTimer(30, [$this, 'checkPunishments']);
        $this->checkPunishments();
    }

    private function debug(...$messages)
    {
        call_user_func_array([$this->logger, 'debug'], $messages);
    }

    /**
     * @return null|void
     */
    public function checkPunishments()
    {
        $this->debug("Checking punishments");

        $strikes = $this->getStrikes();
        foreach ($strikes as $strike) {
            $dt = Carbon::instance($strike->getInsertDate());
            if ($strike->getDuration() > 0 && $dt->diffInSeconds() > $strike->getDuration()) {
                $this->unpunish($strike)
                    ->then(
                        function () use ($strike) {
                            $strike->setResolved(true);
                            $this->manager->flush($strike);
                        }
                    )
                    ->otherwise(
                        function ($error) {
                            $this->logger->error("Failed to unpunish: ", $error);
                        }
                    );
            }
        }
    }

    /**
     * @param Strike $strike
     *
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    private function unpunish(Strike $strike) : PromiseInterface
    {
        $cls = $strike->getAction();
        /** @var AbstractPunishment $punishment */
        $punishment = new $cls;

        return $punishment->resolve($strike);
    }

    /**
     * @return array|Strike[]
     */
    private function getStrikes() : array
    {
        $repo = $this->manager->getRepository(Strike::class);

        return $repo->findBy(['resolved' => false]);
    }
}
