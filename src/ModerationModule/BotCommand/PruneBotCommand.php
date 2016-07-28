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
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Role;
use Discord\Parts\User\Member;
use Doctrine\ORM\ORMException;
use LFGamers\Discord\AbstractBotCommand;
use LFGamers\Discord\Helper\ChannelHelper;
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
 * PruneBotCommand Class
 */
class PruneBotCommand extends AbstractBotCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this
            ->setName('prune')
            ->setDescription('Prunes messages.')
            ->setHelp(
                <<<EOF
Use the following to moderate:

`prune` to get this help message
`prune \d+` to prune the last x messages, where d is any number
`prune @user \d+` to prune the last x message, of the given user
`prune all` restricted to admin, will prune entire channel

all of these can also be used with `purge` instead of `prune`
EOF
            );
    }

    /**
     * @return void
     */
    public function setHandlers()
    {
        $this->responds(
            '/^(prune|purge)(?:s)?(\s+)?(help)?$/i',
            function (Request $request) {
                $request->reply($this->getHelp());
            }
        );

        $this->responds('/^(?:prune|purge)\s+(?<count>\d+)$/', [$this, 'purge']);
        $this->responds('/^(?:prune|purge)\s+all$/', [$this, 'purgeAll']);
        $this->responds('/^(?:prune|purge)\s+<@!?(?<user>\d+)>\s+(?<count>\d+)$/', [$this, 'purge']);
    }

    protected function purgeAll(Request $request)
    {
        if (!$this->isAllowed($request->getGuildAuthor(), 'moderation.purge.all')) {
            $this->logger->info("<@{$request->getAuthor()->id}> tried to purge all.");

            return;
        }

        ChannelHelper::getChannelHistory($request->getChannel())
            ->then(
                function ($messages) use ($request) {
                    $messages[] = $request->getMessage();
                    $request->reply("Deleting ".(sizeof($messages) - 1)." messages", 0, 3);
                    ChannelHelper::deleteMessages($request->getChannel(), $messages)
                        ->otherwise(
                            function ($error) use ($request) {
                                $this->logger->error($error->getMessage());
                                $this->logger->error($error->getTraceAsString());
                                $request->reply(':thumbsdown::skin-tone-2:');
                            }
                        );
                }
            )
            ->otherwise(
                function ($error) use ($request) {
                    $this->logger->error($error->getMessage());
                    $this->logger->error($error->getTraceAsString());
                    $request->reply(':thumbsdown::skin-tone-2:');
                }
            );
    }

    protected function purge(Request $request, array $matches)
    {
        if (!$this->isAllowed($request->getGuildAuthor(), 'moderation.purge')) {
            $this->logger->info("<@{$request->getAuthor()->id}> tried to purge.");

            return;
        }

        $channel = $request->getChannel();
        $this->logger->info("Purging: ".$channel->name);
        $toDelete = $matches['count'] + 1;
        if (empty($matches['user'])) {
            ChannelHelper::getChannelHistory($channel, $toDelete)
                ->then(
                    function ($messages) use ($request) {
                        $messages[] = $request->getMessage();
                        $request->reply("Deleting ".(sizeof($messages) - 2)." messages", 0, 3);
                        ChannelHelper::deleteMessages($request->getChannel(), $messages);
                    }
                )
                ->otherwise(
                    function ($error) use ($request) {
                        $this->logger->error($error->getMessage());
                        $this->logger->error($error->getTraceAsString());
                        $request->reply(':thumbsdown::skin-tone-2:');
                    }
                );
        } else {
            ChannelHelper::getChannelHistory($channel, 300)
                ->then(
                    function (array $messages) use ($request, $matches, $toDelete) {
                        $request->getServer()->members->fetch($matches['user'])
                            ->then(
                                function (Member $member) use ($request, $messages, $toDelete) {
                                    $delete = [$request->getMessage()];
                                    /** @var Message $message */
                                    foreach ($messages as $index => $message) {
                                        if ($message->author->id === $member->id) {
                                            $delete[] = $message;
                                        }
                                    }

                                    array_splice($delete, $toDelete);

                                    $request->reply("Deleting ".(sizeof($delete) - 1)." messages", 0, 3);
                                    ChannelHelper::deleteMessages($request->getChannel(), $delete);
                                }
                            )
                            ->otherwise(
                                function ($error) use ($request) {
                                    $this->logger->error($error->getMessage());
                                    $this->logger->error($error->getTraceAsString());
                                    $request->reply(':thumbsdown::skin-tone-2:');
                                }
                            );
                    }
                )
                ->otherwise(
                    function ($error) use ($request) {
                        $this->logger->error($error->getMessage());
                        $this->logger->error($error->getTraceAsString());
                        $request->reply(':thumbsdown::skin-tone-2:');
                    }
                );
        }
    }
}
