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
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use LFGamers\Discord\Helper\ChannelHelper;
use LFGamers\Discord\Model\Rule;
use LFGamers\Discord\Model\Server;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * RuleBotCommand Class
 */
class RulesBotCommand extends AbstractBotCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('rules')
            ->setDescription('Sets the rules for the current server')
            ->setAdminCommand(true)
            ->setHelp(
                <<<EOF
This command helps with setting rules for the current server

`rules channel <channel>` - Sets the channel where rules are displayed
`rules re-render` - Re-renders the rules
`rules set <position> <type> <value>` Sets the type (text/subtext/strikes) for the rule at the given position
EOF
            );
    }

    /**
     * @return void
     */
    public function setHandlers()
    {
        $this->responds(
            '/^rules$/i',
            function (Request $request) {
                $request->reply($this->getHelp());
            }
        );

        $this->responds('/^rules channel <#(\d+)>/', [$this, 'setRulesChannel']);
        $this->responds(
            '/^rules r?e?-?render/',
            function (Request $request) {
                /** @type Server $server */
                $server = $this->getManager()->getRepository('LFG:Server')->findOneBy(
                    ['server' => $request->getDatabaseServer()]
                );

                $this->renderRules($request, $server);
            }
        );
        $this->responds('/^rules set (\d+) (text|subtext|strikes) ([\S\s]+)/', [$this, 'setRule']);
    }

    protected function setRule(Request $request, array $matches)
    {
        /** @type Server $server */
        $server   = $this->getManager()->getRepository('LFG:Server')->findOneBy(
            ['server' => $request->getDatabaseServer()]
        );
        $position = $matches[1];
        $type     = $matches[2];
        $value    = $matches[3];

        $repo = $this->getManager()->getRepository('LFG:Rule');
        $rule = $repo->findOneBy(['server' => $server, 'position' => $position]);
        if (empty($rule)) {
            $rule = new Rule();
            $rule->setServer($server);
            $rule->setPosition($position);
            $this->getManager()->persist($rule);
        }

        $method = 'set'.ucfirst($type);
        $rule->$method($value);

        $this->getManager()->flush();
        $request->reply("Rule updated");

        $this->renderRules($request, $server);
    }

    protected function setRulesChannel(Request $request, array $matches)
    {
        /** @type Server $server */
        $server = $this->getManager()->getRepository('LFG:Server')->findOneBy(
            ['server' => $request->getDatabaseServer()]
        );
        $server->setRuleChannel($matches[1]);

        $this->getManager()->flush();

        $request->reply('Rule channels set to <#'.$matches[1].'>');
        $request->reply('Make sure I have `Manage Messages` and `Send Messages`.');

        $this->renderRules($request, $server);
    }

    private function renderRules(Request $request, Server $server)
    {
        /** @type Channel $channel */
        $channel = $request->getServer()->channels->get('id', $server->getRuleChannel());

        $this->pruneChannel($channel);
        $request->sendMessage($channel, $request->renderTemplate('@Moderation/rules/rules.md.twig'));

        foreach ($server->getRules() as $rule) {
            if (empty($rule->getPosition()) || empty($rule->getText())) {
                continue;
            }

            $request->sendMessage(
                $channel,
                $request->renderTemplate('@Moderation/rules/rule.md.twig', ['rule' => $rule])
            );
        }

        $request->sendMessage($channel, $request->renderTemplate('@Moderation/rules/strikeSystem.md.twig'));
        $request->sendMessage($channel, $request->renderTemplate('@Moderation/rules/suggestions.md.twig'));
    }

    private function pruneChannel(Channel $channel)
    {
        $this->logger->info("Pruning messages.");

        ChannelHelper::pruneChannel($channel);
    }
}
