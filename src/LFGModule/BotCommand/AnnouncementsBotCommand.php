<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\LFGModule\BotCommand;

use Discord\Base\Request;
use Discord\Parts\Channel\Channel;
use LFGamers\Discord\AbstractBotCommand;
use LFGamers\Discord\Model\Announcement;
use LFGamers\Discord\Model\Server;

/**
 * @author Aaron Scherer <aaron@lfgame.rs>
 *
 * AnnouncementsBotCommand Class
 */
class AnnouncementsBotCommand extends AbstractBotCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('announcements')
            ->setDescription('Manages announcements')
            ->setAdminCommand(true)
            ->setHelp(
                <<<EOF
Use the following to manage announcements:

`announcements toggle` to toggle announcements in the current server
`announcements channel [<#channel>]` to set the announcements channel, or get it
`announcements list [--page=\d+]` to list all the announcements (paginated)
`announcements view <\d+>` to view an announcement
`announcements remove <\d+>` removes the given announcement
`announcements add <title> <\S\s+>` creates an announcement with the given title
`announcements config <option> [<value>]` gets the given config option, or sets it if a value is passed
EOF
            );
    }

    /**
     * @return void
     */
    public function setHandlers()
    {
        $this->responds(
            '/^ann(?:ouncements)? (\s+)?(help)?$/i',
            function (Request $request) {
                $request->reply($this->getHelp());
            }
        );

        $this->responds('/^ann(?:ouncements)? status$/i', [$this, 'getStatus']);
        $this->responds('/^ann(?:ouncements)? toggle$/i', [$this, 'toggleAnnouncements']);
        $this->responds('/^ann(?:ouncements)? channel$/i', [$this, 'getAnnouncementsChannel']);
        $this->responds('/^ann(?:ouncements)? channel <#(?<channel>\d+)>$/i', [$this, 'setAnnouncementsChannel']);
        $this->responds('/^ann(?:ouncements)? config (?<option>[A-Za-z-_]+)$/i', [$this, 'getAnnouncementsConfig']);
        $this->responds('/^ann(?:ouncements)? list(?: --page=(?<page>\d+))?$/i', [$this, 'listAnnouncements']);
        $this->responds('/^ann(?:ouncements)? (?:view|get) (?<id>\d+)$/i', [$this, 'getAnnouncement']);

        $this->responds(
            '/^ann(?:ouncements)? (?:add|create) (?<title>[A-Za-z-_]+) (?<content>[\S\s]+)$/i',
            [$this, 'addAnnouncement']
        );
        $this->responds(
            '/^ann(?:ouncements)? config (?<option>[A-Za-z-_]+) (?<value>.*)/i',
            [$this, 'setAnnouncementsConfig']
        );
    }

    public function getStatus(Request $request)
    {
        /** @var Server $server */
        $server = $request->getDatabaseServer();

        $request->reply('Announcements are currently '.($server->isAnnouncementsEnabled() ? 'enabled' : 'disabled'));
    }

    public function getAnnouncement(Request $request, array $matches)
    {
        /** @var Server $server */
        $server = $request->getDatabaseServer();
        $id     = $matches['id'];
        foreach ($server->getAnnouncements() as $announcement) {
            if ($announcement->getId() === (int) $id) {
                return $request->reply("Announcement Demo:\n\n".$announcement->getContent());
            }
        }

        $request->reply("There is no announcement with that ID.");
    }

    public function listAnnouncements(Request $request, array $matches)
    {
        /** @var Server $server */
        $server = $request->getDatabaseServer();
        $page   = array_key_exists('page', $matches) ? (int) $matches['page'] : 1;

        $request->reply(
            $request->renderTemplate(
                '@LFGModule/Announcement/list.md.twig',
                ['page' => $page, 'announcements' => $server->getAnnouncements()]
            )
        );
    }

    /**
     * @param Request $request
     * @param array   $matches
     */
    protected function addAnnouncement(Request $request, array $matches)
    {
        /** @var Server $server */
        $server       = $request->getDatabaseServer();
        $announcement = new Announcement();
        $announcement->setTitle($matches['title']);
        $announcement->setContent($matches['content']);
        $announcement->setServer($server);
        $this->getManager()->persist($announcement);

        $this->getManager()->flush($announcement);

        $request->reply("Announcement Created.");
    }

    protected function toggleAnnouncements(Request $request)
    {
        /** @var Server $server */
        $server  = $request->getDatabaseServer();
        $enabled = !$server->isAnnouncementsEnabled();

        $config = $this->getServerConfig($request);
        if ($enabled) {
            if (!isset($config['frequency'])) {
                return $request->reply(
                    "Please set the average frequency of posts. `announcements config frequency <value>`"
                );
            }
            if (!isset($config['minimum_messages'])) {
                return $request->reply(
                    "Please set the minimum number of messages between of posts. ".
                    "`announcements config minimum_messages <value>`"
                );
            }
            if (empty($server->getAnnouncementsChannel())) {
                return $request->reply(
                    "Please set the announcement channel. `announcements channel <#channel>`"
                );
            }
            if ($server->getAnnouncements()->count() <= 0) {
                return $request->reply("Please add some announcements first.");
            }
        }

        $server->setAnnouncementsEnabled($enabled);
        $this->getManager()->flush($server);

        $request->reply('Announcements are now '.($enabled ? 'enabled' : 'disabled').'.');
    }

    protected function setAnnouncementsChannel(Request $request, array $matches)
    {
        /** @var Server $server */
        $server = $request->getDatabaseServer();

        /** @var Channel $channel */
        $channel = $request->getServer()->channels->get('id', $matches['channel']);
        if (empty($channel) || $channel->getChannelType() === 'voice') {
            return $request->reply(sprintf('<#%d> is not a valid channel.', $channel->id));
        }

        $server->setAnnouncementsChannel($channel->id);
        $this->getManager()->flush($server);

        $request->reply('Announcements channel set as <#'.$channel->id.'>.');
    }

    protected function getAnnouncementsChannel(Request $request)
    {
        /** @var Server $server */
        $server = $request->getDatabaseServer();

        $request->reply(sprintf("The current announcements channel is <#%d>", $server->getAnnouncementsChannel()));
    }

    protected function getAnnouncementsConfig(Request $request, array $matches)
    {
        $config = $this->getServerConfig($request);
        if (array_key_exists($matches['option'], $config)) {
            return $request->reply($config[$matches['option']]);
        }

        return $request->reply("There is no value for that config.");
    }

    protected function setAnnouncementsConfig(Request $request, array $matches)
    {
        $key = 'announcements.'.$request->getServer()->id;

        $config                     = $this->getServerConfig($request);
        $config[$matches['option']] = $matches['value'];
        $this->setConfig($key, json_encode($config));

        return $request->reply("Config value has been set.");
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getServerConfig(Request $request)
    {
        $key = 'announcements.'.$request->getServer()->id;

        $config = $this->getConfig($key);
        if (empty($config)) {
            $this->setConfig($key, '{}');

            return [];
        }

        return json_decode($config->getValue(), true);
    }
}
