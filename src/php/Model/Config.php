<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * User Class
 * @ORM\Entity
 * @ORM\Table(name="config")
 */
class Config
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, name="config_key")
     */
    protected $key;

    /**
     * @var string
     * @ORM\Column(type="blob", name="config_value")
     */
    protected $value;

    /**
     * Config constructor.
     *
     * @param $key
     * @param $value
     */
    public function __construct($key, $value = null)
    {
        $this->key   = $key;
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Config
     */
    public function setId(int $id) : Config
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return Config
     */
    public function setKey(string $key) : Config
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return Config
     */
    public function setValue(string $value) : Config
    {
        $this->value = $value;

        return $this;
    }
}
