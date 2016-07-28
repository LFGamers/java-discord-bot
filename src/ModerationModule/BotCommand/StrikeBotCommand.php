<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\ModerationModule\BotCommand;

use Discord\Base\AppBundle\Event\ServerEvent;
use Discord\Base\Request;
use Discord\Parts\Guild\Role;
use Discord\Parts\User\Member;
use Doctrine\ORM\ORMException;
use LFGamers\Discord\AbstractBotCommand;
use LFGamers\Discord\Helper\RoleHelper;
use LFGamers\Discord\Helper\UserHelper;
use LFGamers\Discord\Model\Punishment;
use LFGamers\Discord\Model\Strike;
use LFGamers\Discord\Model\User;
use LFGamers\Discord\ModerationModule\Punishment\AbstractPunishment;
use LFGamers\Discord\ModerationModule\Punishment\Mute;
use LFGamers\Discord\ModerationModule\Punishment\PermanentBan;
use LFGamers\Discord\ModerationModule\Punishment\TemporaryBan;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * StrikeBotCommand Class
 */
class StrikeBotCommand extends AbstractBotCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this
            ->setName('strike')
            ->setDescription('Manages strikes for users.')
            ->setHelp(
                <<<EOF
Use the following to moderate:

`strike @user <Reason>` to give a user a strike
`strikes @user` to get the strike history of a user
EOF
            );
    }

    /**
     * @return void
     */
    public function setHandlers()
    {
        $this->responds(
            '/^strike(?:s)?(\s+)?(help)?$/i',
            function (Request $request) {
                $request->reply($this->getHelp());
            }
        );

        $this->responds('/^strike(?:\s+)<@!?(?<user>\d+)>(?:\s+)?$/', [$this, 'requireReason']);
        $this->responds('/^strike(?:\s+)<@!?(?<user>\d+)>(?:\s+)?(?<reason>.*)?$/i', [$this, 'giveStrike']);
        //$this->responds('/^destrike(?:\s+)<@!?(?<user>\d+)> (\d+)/i', [$this, 'destrike']);
        $this->responds('/^strikes(?:\s+)?<@!?(?<user>\d+)>/i', [$this, 'viewStrikes']);
    }

    protected function requireReason(Request $request)
    {
        return $request->reply("You must specify a reason.");
    }

    protected function giveStrike(Request $request, array $matches)
    {
        if ($request->isPrivateMessage()) {
            $this->logger->info("<@{$request->getAuthor()->id}> tried to punish in PM.");

            return;
        }

        if (!$this->isAllowed($request->getGuildAuthor(), 'moderation.strike.give')) {
            $this->logger->info("<@{$request->getAuthor()->id}> tried to give someone a strike.");

            return;
        }

        if (empty($matches['reason'])) {
            return $request->reply("Must specify a reason.");
        }

        UserHelper::getMember($this->getClientUser($matches['user']), $request->getServer())
            ->then(
                function (Member $member) use ($request, $matches) {
                    try {
                        if (RoleHelper::userHasRole($member, 'Staff') || RoleHelper::userHasRole($member, 'Bot')) {
                            return $request->reply(":thumbsdown::skin-tone-2: Can't punish staff.");
                        }
                    } catch (\Exception $e) {
                        return $request->reply(":thumbsdown::skin-tone-2: This server is not set up for strikes.");
                    }

                    $user           = $this->getDatabaseUser($matches['user']);
                    $strikes        = $this->getStrikes($user);
                    $punishmentType = $this->getPunishment($strikes);

                    /** @var AbstractPunishment $punishment */
                    $punishment = new $punishmentType['action']($this->getDiscord());

                    $strike = new Strike();
                    $strike->setInsertDate(new \DateTime());
                    $strike->setModerator($this->getDatabaseUser($request->getAuthor()->id));
                    $strike->setUser($user);
                    $strike->setReason($matches['reason']);
                    $strike->setServer($request->getDatabaseServer());

                    $strike->setAction($punishmentType['action']);
                    if (isset($punishmentType['duration'])) {
                        $strike->setDuration($punishmentType['duration']);
                    }

                    $punishment->perform($strike)
                        ->then(
                            function () use ($strike, $request, $member) {
                                $this->getManager()->persist($strike);
                                try {
                                    $this->getManager()->flush();
                                } catch (ORMException $e) {
                                    $this->getManager()->getConnection()->connect();
                                    $this->getManager()->flush();
                                }

                                $request->reply(':thumbsup::skin-tone-2:');

                                $event = ServerEvent::create(
                                    $request->getServer(),
                                    'moderation_action',
                                    $strike,
                                    $member
                                );
                                $this->container->get('event_dispatcher')->dispatch($event);
                            }
                        )
                        ->otherwise(
                            function (\Exception $e) use ($request) {
                                $this->logger->error($error->getTraceAsString());
                                return $request->reply(
                                    ":thumbsdown::skin-tone-2: This server is not set up for strikes."
                                );
                            }
                        );
                },
                function ($error) use ($request) {
                    $this->logger->error($error->getTraceAsString());
                    $request->reply(":thumbsdown::skin-tone-2: Failed to punish user.");
                }
            )
            ->otherwise(
                function ($error) use ($request) {
                    $this->logger->error($error->getMessage());
                    $request->reply(":thumbsdown::skin-tone-2: Failed to punish user.");
                }
            );
    }

    protected function viewStrikes(Request $request, array $matches)
    {
        if ($request->isPrivateMessage()) {
            return;
        }

        if (!$this->isAllowed($request->getGuildAuthor(), 'moderation.strike.view')) {
            return;
        }

        $output = '';
        foreach ($this->getStrikes($this->getDatabaseUser($matches['user'])) as $index => $strike) {
            $output .= sprintf(
                "\n[%s] [Strike **%d**] from <@%d> \n\n```\n%s\n```\n---\n",
                $strike->getInsertDate()->format('Y-m-d h:i:sa T'),
                $index + 1,
                $strike->getModerator()->getIdentifier(),
                $strike->getReason()
            );
        }

        if ($output === '') {
            $request->reply('That user has no strikes.');

            return;
        }

        $request->reply(substr($output, 0, -4));
    }

    /**
     * @param User $user
     *
     * @return array|Strike[]
     */
    private function getStrikes(User $user)
    {
        $qb = $this->getManager()->getRepository(Strike::class)->createQueryBuilder('s');
        $qb->select('s')
            ->where('s.user = ?1')
            ->orderBy('s.insertDate', 'asc')
            ->setParameter(1, $user);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array|Strike[] $strikes
     *
     * @return array
     */
    private function getPunishment(array $strikes) : array
    {
        switch (sizeof($strikes)) {
            case 0:
                return ['action' => Mute::class, 'duration' => 30];
            case 1:
                return ['action' => TemporaryBan::class, 'duration' => 60 * 60 * 24];
            default:
                return ['action' => PermanentBan::class];
        }
    }
}
