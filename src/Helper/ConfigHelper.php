<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use LFGamers\Discord\Model\Config;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ConfigHelper Class
 */
class ConfigHelper
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * ConfigHelper constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityRepository
     */
    protected function getRepository() : EntityRepository
    {
        return $this->entityManager->getRepository(Config::class);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->getRepository()->findOneByKey($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return ConfigHelper
     */
    public function set($key, $value)
    {
        $item = $this->get($key);
        if (empty($item)) {
            $item = new Config($key);
            $this->entityManager->persist($item);
        }

        $item->setValue($value);

        $this->entityManager->flush($item);

        return $this;
    }
}
