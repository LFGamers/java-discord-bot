<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\FunModule\BotCommand;

use Discord\Base\Request;
use LFGamers\Discord\AbstractBotCommand;
use LFGamers\Discord\Helper\UserHelper;
use LFGamers\Discord\Model\Karma;
use LFGamers\Discord\Model\KarmaLog;
use LFGamers\Discord\Model\Server;
use LFGamers\Discord\Model\User;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * KarmaBotCommand Class
 */
class KarmaBotCommand extends AbstractBotCommand
{
    /**
     * Array of plus messages
     */
    const POSITIVE_MESSAGES = [
        'has pleased the gods!',
        'is in good favor!',
        'is ascending!',
        'is doing good things!',
        'leveled up!',
        'is on the rise!',
        '+1!'
    ];

    /**
     * Array of negative messages
     */
    const NEGATIVE_MESSAGES = [
        'has upset the gods!',
        'is in poor favor!',
        'is descending!',
        'is doing bad things...',
        'took a hit! Ouch.',
        'lost a level.',
        'lost a life.',
        'took a dive.',
        '-1!'
    ];

    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('karma')
            ->setDescription('Manages karma. Type help karma for more info')
            ->setHelp(
                <<<EOF
Use the following to manage karma:

`@user++` or `@user ++` to give user some of your karma
`karma top` to list the top karma
`karma bottom` to list the worst karma
`karma clear` to clear the karma (must be admin)
`karma give @user` to feed the pool with some admin karma
`karma remove @user` to starve the pool of some admin karma
EOF
            );
    }

    /**
     * @return void
     */
    public function setHandlers()
    {
        $this->responds(
            '/^karma(\s+)?(help)?$/i',
            function (Request $request) {
                $request->reply($this->getHelp());
            }
        );

        $this->hears('/<@!?(\d+)>(?:\s+)?\+\+/', [$this, 'giveUserKarma']);
        $this->responds('/^karma (top|best|bottom|worst)(?:\s+)?(\d+)?$/i', [$this, 'showKarma']);
        $this->responds('/^karma clear$/i', [$this, 'clearKarma']);
        $this->responds('/^karma <@!?(\d+)>$/i', [$this, 'getKarma']);
        $this->responds('/^karma (give|add|remove|take) <@!?(\d+)>$/i', [$this, 'staffKarma']);
    }

    /**
     * @param Request $request
     * @param array   $matches
     *
     * @return \Discord\Parts\Channel\Message|void|null
     * @throws \Exception
     */
    protected function staffKarma(Request $request, array $matches)
    {
        if ($request->isPrivateMessage()) {
            return $request->reply("This must be ran in a server.");
        }

        if (!$this->isAllowed($request->getGuildAuthor(), 'karma.staff')) {
            return;
        }

        /** @var \Discord\Parts\User\User $clientUser */
        $clientUser = $this->discord->users->get('id', $matches[2]);
        $user       = $this->getManager()->getRepository(User::class)->findOneByIdentifier($clientUser->id);
        if (empty($user)) {
            $user = new User();
            $user->setIdentifier($clientUser->id);
            $this->getManager()->persist($user);
        }

        if ($request->getAuthor()->id === $clientUser->id) {
            return $request->reply("... Nice try.");
        }

        $give   = $matches[1] === 'give' || $matches[1] === 'add';
        $phrase = $give
            ? self::POSITIVE_MESSAGES[array_rand(self::POSITIVE_MESSAGES)]
            : self::NEGATIVE_MESSAGES[array_rand(self::NEGATIVE_MESSAGES)];

        $karma = $give ? 1 : -1;
        $user->setKarma($user->getKarma() + $karma);
        $this->getManager()->flush($user);

        $request->reply(
            $request->renderTemplate(
                '@Fun/Karma/give.md.twig',
                [
                    'phrase' => $phrase,
                    'user'   => $user,
                ]
            )
        );
        $this->logger->debug("User has perms");
    }

    protected function giveUserKarma(Request $request, array $matches)
    {
        if (!$this->container->getParameter('features')['karma']['enabled']) {
            return;
        }

        if ($request->isPrivateMessage()) {
            return $request->reply("This must be ran in a server.");
        }

        /** @var \Discord\Parts\User\User $clientUser */
        $clientUser = $this->discord->users->get('id', $matches[1]);
        $user       = $this->getManager()->getRepository(User::class)->findOneByIdentifier($clientUser->id);
        if (empty($user)) {
            $user = new User();
            $user->setIdentifier($clientUser->id);
            $this->getManager()->persist($user);
        }

        if ($request->getAuthor()->id === $clientUser->id) {
            return $request->reply("... Nice try.");
        }

        /** @var User $author */
        $author = $this->getManager()->getRepository(User::class)->findOneByIdentifier($request->getAuthor()->id);
        if ($author->getKarma() < 1) {
            return $request->reply("You don't have any karma to give!");
        }

        $phrase = self::POSITIVE_MESSAGES[array_rand(self::POSITIVE_MESSAGES)];

        $author->setKarma($author->getKarma() - 1);
        $user->setKarma($user->getKarma() + 1);
        $karmaLog = new KarmaLog();
        $karmaLog->setRecipient($user);
        $karmaLog->setSender($author);
        $karmaLog->setInsertDate(new \DateTime());
        $this->getManager()->persist($karmaLog);
        $this->getManager()->flush();

        $request->reply(
            $request->renderTemplate(
                '@Fun/Karma/give.md.twig',
                [
                    'phrase' => $phrase,
                    'user'   => $user,
                ]
            )
        );
    }

    protected function showKarma(Request $request, array $matches)
    {
        if ($request->isPrivateMessage()) {
            return $request->reply("This must be ran in a server.");
        }

        $sort  = ($matches[1] === 'top' || $matches[1] === 'best') ? 'desc' : 'asc';
        $limit = !empty($matches[2]) ? (int) $matches[2] : 5;

        $qb = $this->getManager()->getRepository(User::class)->createQueryBuilder('u');
        $qb->where('u.karma != 0');
        $qb->orderBy('u.karma', $sort);
        $qb->setMaxResults($limit);

        $users = $qb->getQuery()->getResult();

        $request->reply(
            $request->renderTemplate(
                '@Fun/Karma/list.md.twig',
                ['users' => $users, 'type' => $matches[1], 'count' => $limit]
            )
        );
    }

    protected function getKarma(Request $request, array $matches)
    {
        if ($request->isPrivateMessage()) {
            return $request->reply("This must be ran in a server.");
        }

        /** @var \Discord\Parts\User\User $clientUser */
        $clientUser = $this->discord->users->get('id', $matches[1]);
        $user       = $this->getManager()->getRepository(User::class)->findOneByIdentifier($clientUser->id);
        if (empty($user)) {
            $user = new User();
            $user->setIdentifier($clientUser->id);
            $this->getManager()->persist($user);
        }

        $request->reply("<@{$clientUser->id}> currently has {$user->getKarma()} karma.");
    }

    protected function clearKarma(Request $request)
    {
        if (!$this->isAllowed(UserHelper::getMember($request->getAuthor(), $request->getServer()), 'karma.clear')) {
            return;
        }

        /** @var Server $dbServer */
        $dbServer = $request->getDatabaseServer();
        foreach ($dbServer->getUsers() as $user) {
            $user->setKarma(0);
        }

        $this->getManager()->flush();
        $request->getMessage()->delete();
        $request->reply('Karma cleared', 0, 3);
    }
}
