<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

use Discord\Base\Bot;
use Doctrine\Common\Annotations\AnnotationRegistry;
use LFGamers\Discord\BaseModule\BaseModule;
use LFGamers\Discord\Model\Server;
use LFGamers\Discord\ModerationModule\ModerationModule;
use LFGamers\Discord\FunModule\FunModule;
use LFGamers\Discord\ServerManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

$loader = require __DIR__.'/../vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$resolver = new OptionsResolver();
$resolver->setRequired(['admin_id', 'token', 'redis_dsn', 'mysql_dsn']);
$resolver->setDefined('log_js_events', 'logged_servers', 'mongo_dsn');
$config = $resolver->resolve(json_decode(file_get_contents(__DIR__.'/../config/config.json'), true));
$bot    = Bot::create(
    [
        'modules'    => [
            BaseModule::class,
            ModerationModule::class,
            FunModule::class
        ],
        'parameters' => [
            'name'                 => 'LFG Bot',
            'version'              => '0.0.1',
            'author'               => 'Looking FOr Gamers',
            'log_dir'              => __DIR__.'/../var/logs/',
            'cache_dir'            => __DIR__.'/../var/cache/',
            'admin_id'             => $config['admin_id'],
            'token'                => $config['token'],
            'prefix'               => '%',
            'status'               => 'https://lfgame.rs',
            'server_class'         => Server::class,
            'server_manager_class' => ServerManager::class
        ],
        'cache'      => [
            'providers' => [
                'chain' => [
                    'factory' => 'cache.factory.chain',
                    'options' => [
                        'services' => [
                            '@cache.provider.array',
                            '@cache.provider.redis',
                        ],
                    ],
                ],
                'array' => [
                    'factory' => 'cache.factory.array',
                ],
                'redis' => [
                    'factory' => 'cache.factory.redis',
                    'options' => ['dsn' => $config['redis_dsn']],
                ],
            ],
        ],
        'databases'  => [
            'mysql'    => [
                'enabled' => true,
                'dsn'     => $config['mysql_dsn'],
            ],
            'mappings' => [
                'LFG' => [
                    'type'   => 'annotation',
                    'dir'    => realpath(__DIR__.'/../src/php/Model'),
                    'prefix' => 'LFGamers\Discord\Model',
                    'alias'  => 'LFG',
                ],
            ],
        ],
    ]
);

$bot->run();
