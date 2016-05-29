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

use Discord\Base\AbstractBotCommand;
use Discord\Base\Request;
use Discord\Parts\User\Member;
use Doctrine\Common\Collections\ArrayCollection;
use LFGamers\Discord\Model\Rule;
use LFGamers\Discord\Model\Strike;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ExampleBotCommand Class
 */
class ExampleBotCommand extends AbstractBotCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('strike')
            ->setDescription('Gives strikes to people for violating rules')
            ->setAdminCommand(true)
            ->setHelp(
                <<<EOF
This command helps with giving strikes to users

`strike <user> <rule number>` - Gives the give user (by mention) a strike
EOF
            );
    }

    /**
     * @return void
     */
    public function setHandlers()
    {
        $this->responds(
            '/^strike/i',
            function (Request $request) {
                $request->reply($this->getHelp());
            }
        );

        $this->responds('/^strike <@(\d+)> #(\d+)/', [$this, 'giveUserStrike']);
    }

    public function giveUserStrike(Request $request, array $matches)
    {
        $user = $request->getMention(0);
        $strikes = $this->getUserStrikes($user);

        if ($this->hasRecentStrike($strikes)) {
            $request->sendMessage($user, 'Someone already gave a strike to that user recently.');
        }
        
        $request->reply($request->renderTemplate('@Moderation/example/example.md.twig'));
    }

    /**
     * @param Member $user
     *
     * @return Strike[]|ArrayCollection
     */
    private function getUserStrikes(Member $user)
    {
        $repo = $this->getManager()->getRepository('LFG:Strike');

        return $repo->findBy(['user' => $user->id]);
    }

    /**
     * @param ArrayCollection|Strike[] $strikes
     */
    private function hasRecentStrike(ArrayCollection $strikes)
    {
        foreach ($strikes as $strike) {
            $minuteAgo = new \DateTime('1 minute ago');
            if ($strike->getInsertDate() < $minuteAgo) {
                $this->logger
            }
        }
    }
}
