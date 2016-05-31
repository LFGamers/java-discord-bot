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

use Discord\Base\AbstractBotCommand;
use Discord\Base\Request;
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

`@user++` or `@user ++` to give user karma
`@user--` or `@user --` to remove user karma
`karma top` to list the top karma
`karma bottom` to list the worst karma
`karma clear` to clear the karma (must be admin)
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

        $this->hears('/<@!?(\d+)>(?:\s+)?(\+\+|\-\-)/', [$this, 'giveUserKarma']);
        $this->responds('/^karma (top|best|bottom|worst)(?:\s+)?(\d+)?$/i', [$this, 'showKarma']);
        $this->responds('/^karma clear$/i', [$this, 'clearKarma']);
        $this->responds('/^karma <@!?(\d+)>$/i', [$this, 'getKarma']);
    }

    public function giveUserKarma(Request $request, array $matches)
    {
        if ($request->isPrivateMessage()) {
            return $request->reply("This must be ran in a server.");
        }

        /** @var \Discord\Parts\User\User $clientUser */
        $clientUser = $this->discord->client->users->get('id', $matches[1]);
        $user       = $this->getManager()->getRepository(User::class)->findOneByIdentifier($clientUser->id);
        if (empty($user)) {
            $user = new User();
            $user->setIdentifier($clientUser->id);
            $this->getManager()->persist($user);
        }

        if ($request->getAuthor()->id === $clientUser->id && $matches[2] === '++') {
            $request->reply("... Nice try.");
            $matches[2] = '--';
        }

        $phrase = $matches[2] === '++'
            ? self::POSITIVE_MESSAGES[array_rand(self::POSITIVE_MESSAGES)]
            : self::NEGATIVE_MESSAGES[array_rand(self::NEGATIVE_MESSAGES)];

        $karma = $matches[2] === '++' ? 1 : -1;
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
    }

    public function showKarma(Request $request, array $matches)
    {
        $sort = $matches[1] === 'top' || $matches[1] === 'best' ? 'desc' : 'asc';
        $limit = !empty($matches[2]) ? (int) $matches[2] : 5;

        $qb = $this->getManager()->getRepository(User::class)->createQueryBuilder('u');
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

    public function getKarma(Request $request, array $matches)
    {
        if ($request->isPrivateMessage()) {
            return $request->reply("This must be ran in a server.");
        }

        /** @var \Discord\Parts\User\User $clientUser */
        $clientUser = $this->discord->client->users->get('id', $matches[1]);
        $user       = $this->getManager()->getRepository(User::class)->findOneByIdentifier($clientUser->id);
        if (empty($user)) {
            $user = new User();
            $user->setIdentifier($clientUser->id);
            $this->getManager()->persist($user);
        }

        $request->reply("<@{$clientUser->id}> currently has {$user->getKarma()} karma.");
    }

    public function clearKarma(Request $request)
    {
        if (!$request->isAdmin()) {
            $request->getMessage()->delete();
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
