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

use Discord\Base\Request;
use LFGamers\Discord\AbstractBotCommand;
use LFGamers\Discord\Helper\UserHelper;
use LFGamers\Discord\Model\User;
use LFGamers\Discord\Model\UserLog;

/**
 * @author Anthony Allan <anthonyjallan@gmail.com>
 *
 * ModLogBotCommand Class
 */
class ModLogBotCommand extends AbstractBotCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('log')
            ->setDescription('Logs moderation action. Type help log for more info')
            ->setHelp(
                <<<EOF
Use the following to moderate:

`log note @user description` to log a note about a user
`log warning @user description` to log that a warning was given to a user
`log strike @user description` to log that a strike was given to a user
`log status @user` too list all logs for a user
EOF
            );
    }

    /**
     * @return void
     */
    public function setHandlers()
    {
        $this->responds(
            '/^log(\s+)?(help)?$/i',
            function (Request $request) {
                $request->reply($this->getHelp());
            }
        );

        $this->responds('/^log status(\s+)?<@!?(\d+)>(\s+)?$/i', [$this, 'getStatus']);
        $this->responds('/^log warning(\s+)?<@!?(\d+)>(\s+)?(?P<message>.*)$/i', [$this, 'logWarning']);
        $this->responds('/^log strike(\s+)?<@!?(\d+)>(\s+)?(?P<message>.*)$/i', [$this, 'logStrike']);
        $this->responds('/^log note(\s+)?<@!?(\d+)>(\s+)?(?P<message>.*)$/i', [$this, 'logNote']);
    }

    protected function getStatus(Request $request, array $matches)
    {
        if (!$this->isAllowed(UserHelper::getMember($request->getAuthor(), $request->getServer()), 'modlog.view')) {
            return;
        }

        $mentionedClientUser = $this->discord->client->users->get('id', $matches[2]);
        $mentionedUser = $this->getManager()->getRepository(User::class)->findOneByIdentifier($mentionedClientUser->id);
        if (empty($mentionedUser)) {
            $mentionedUser = new User();
            $mentionedUser->setIdentifier($mentionedClientUser->id);
            $this->getManager()->persist($mentionedUser);
        }

        /** @var UserLog[] $logs **/
        $logs = $this->getManager()->getRepository(UserLog::class)->findBySubject($mentionedUser);

        $output = '';

        foreach ($logs as $log) {
            $output .= sprintf(
                "\n[%s] [**%s**] from <@!%d> \n\n```\n%s\n```---\n",
                $log->getDatetime()->format('Y-m-d H:i:s e'),
                ucfirst($log->getCategory()),
                $log->getAuthor()->getIdentifier(),
                $log->getMessage() ?: 'No message'
            );
        }

        if ($output === '') {
            $request->reply('That user has nothing in the logs.');
            return;
        }

        $request->reply(substr($output,0,-4));
    }

    protected function logWarning(Request $request, array $matches)
    {
        $this->logSomething($request, $matches, 'warning');
    }

    protected function logStrike(Request $request, array $matches)
    {
        $this->logSomething($request, $matches, 'strike');
    }

    protected function logNote(Request $request, array $matches)
    {
        $this->logSomething($request, $matches, 'note');
    }

    protected function logSomething(Request $request, array $matches, $category)
    {
        if (!$this->isAllowed(UserHelper::getMember($request->getAuthor(), $request->getServer()), 'modlog.log')) {
            return;
        }

        $mentionedClientUser = $this->discord->client->users->get('id', $matches[2]);
        $mentionedUser = $this->getManager()->getRepository(User::class)->findOneByIdentifier($mentionedClientUser->id);
        if (empty($mentionedUser)) {
            $mentionedUser = new User();
            $mentionedUser->setIdentifier($mentionedClientUser->id);
            $this->getManager()->persist($mentionedUser);
        }

        $authorClientUser = $request->getAuthor();
        $authorUser = $this->getManager()->getRepository(User::class)->findOneByIdentifier($authorClientUser->id);
        if (empty($authorUser)) {
            $authorUser = new User();
            $authorUser->setIdentifier($authorClientUser->id);
            $this->getManager()->persist($authorUser);
        }

        $userlog = new UserLog();
        $userlog->setAuthor($authorUser);
        $userlog->setSubject($mentionedUser);
        $userlog->setDatetime(new \DateTime());
        $userlog->setCategory($category);
        $userlog->setMessage($matches['message']);
        $this->getManager()->persist($userlog);
        $this->getManager()->flush();

        $request->reply('Done!');
    }
}
