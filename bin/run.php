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
use LFGamers\Discord\LFGModule\LFGModule;
use LFGamers\Discord\ModerationModule\ModerationModule;
use LFGamers\Discord\FunModule\FunModule;
use LFGamers\Discord\ServerManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

$loader = require __DIR__.'/../vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$resolver = new OptionsResolver();
$resolver->setRequired(['admin_id', 'token', 'redis_dsn', 'mysql_dsn', 'features']);
$resolver->setDefined(['log_js_events', 'logged_servers', 'mongo_dsn']);
$config = $resolver->resolve(json_decode(file_get_contents(__DIR__.'/../config/config.json'), true));
$bot    = Bot::create(
    [
        'modules'    => [
            BaseModule::class,
            ModerationModule::class,
            FunModule::class,
            LFGModule::class
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
            'status'               => [
                'name' => 'https://lfgame.rs',
                'url'  => 'https://lfgame.rs',
                'type' => 1
            ],
            'server_class'         => Server::class,
            'server_manager_class' => ServerManager::class,
            'features'             => $config['features']
        ],
        'cache'      => [
            'providers' => [
                'array' => [
                    'factory' => 'cache.factory.array',
                ],
                'chain' => [
                    'factory' => 'cache.factory.chain',
                    'options' => [
                        'services' => [
                            '@cache.provider.array',
                            '@cache.provider.redis',
                        ],
                    ],
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
                    'type'      => 'annotation',
                    'dir'       => realpath(__DIR__.'/../src/Model'),
                    'prefix'    => 'LFGamers\Discord\Model',
                    'alias'     => 'LFG',
                ],
            ],
        ],
    ]
);

$bot->run();
