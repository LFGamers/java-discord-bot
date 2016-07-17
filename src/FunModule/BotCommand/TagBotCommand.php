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
use Discord\Parts\Channel\Message;
use LFGamers\Discord\AbstractBotCommand;
use LFGamers\Discord\Helper\UserHelper;
use LFGamers\Discord\Model\Karma;
use LFGamers\Discord\Model\KarmaLog;
use LFGamers\Discord\Model\Server;
use LFGamers\Discord\Model\Tag;
use LFGamers\Discord\Model\User;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * TagBotCommand Class
 */
class TagBotCommand extends AbstractBotCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('tag')
            ->setDescription('Manages tags. Type help tag for more info')
            ->setHelp(
                <<<EOF
Use the following to manage karma:

`tag <name>` to get the given tag
`tag <name> .*` to set the given tag
`tags` to list all the tags
EOF
            );
    }

    /**
     * @return void
     */
    public function setHandlers()
    {
        $this->responds(
            '/^tags?\s+help$/i',
            function (Request $request) {
                $request->reply($this->getHelp());
            }
        );

        $this->responds('/^tags$/i', [$this, 'getTags']);
        $this->responds('/^tag (?<name>[A-Za-z0-0-_]+)$/i', [$this, 'getTag']);
        $this->responds('/^tag (?<name>[A-Za-z0-0-_]+)\s+(?<content>[\S\s]+)/i', [$this, 'setTag']);
    }

    /**
     * @param Request $request
     */
    protected function getTags(Request $request)
    {
        /** @var Tag[] $tags */
        $repo = $this->getManager()->getRepository(Tag::class);
        $tags = $repo->findAll();

        $content = "Here are all the registered tags: \n\n";
        foreach ($tags as $tag) {
            $content .= $tag->getName().', ';
        }
        $content = trim($content, ', ')."\n\n";

        $request->reply($content);
    }

    /**
     * @param Request $request
     * @param array   $matches
     *
     * @return \React\Promise\Promise
     */
    protected function getTag(Request $request, array $matches)
    {
        /** @var Tag $tag */
        $repo = $this->getManager()->getRepository(Tag::class);
        $tag  = $repo->findOneByName($matches['name']);

        if (empty($tag)) {
            return $request->reply('No tag with that name found.');
        }

        $request->reply($tag->getValue());
    }

    /**
     * @param Request $request
     * @param array   $matches
     */
    protected function setTag(Request $request, array $matches)
    {
        /** @var Tag $tag */
        $repo = $this->getManager()->getRepository(Tag::class);
        $tag  = $repo->findOneByName($matches['name']);

        if (empty($tag)) {
            $tag = new Tag();
            $tag->setName($matches['name']);
            $this->getManager()->persist($tag);
        }

        $tag->setValue($matches['content']);
        $this->getManager()->flush($tag);

        $request->reply(':thumbsup::skin-tone-2:');
    }
}
