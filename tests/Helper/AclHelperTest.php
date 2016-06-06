<?php

/*
 * This file is part of discord-bot.
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace LFGamers\Discord\Test\Helper;

use Doctrine\ORM\EntityManager;
use LFGamers\Discord\Helper\AclHelper;
use Monolog\Logger;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * AclHelperTest Class
 */
class AclHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var AclHelper
     */
    private $helper;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->createMock(EntityManager::class);
        $this->logger = $this->createMock(Logger::class);

        $this->helper = new AclHelper($this->em, $this->logger);
    }

    public function testIsWildcardMatch()
    {
        $perm = 'test.value.is.allowed';

        $this->assertTrue($this->helper->isWildcardMatch('test.value.is.*', $perm));
        $this->assertFalse($this->helper->isWildcardMatch('test.value.isnt.*', $perm));
        $this->assertTrue($this->helper->isWildcardMatch('test.value.*', $perm));
        $this->assertFalse($this->helper->isWildcardMatch('test.othervalue.*', $perm));
        $this->assertTrue($this->helper->isWildcardMatch('test.*', $perm));
        $this->assertFalse($this->helper->isWildcardMatch('foo.*', $perm));
        $this->assertTrue($this->helper->isWildcardMatch('*', $perm));
    }

    public function testDoesPermissionMatch()
    {
        $perm = 'test.value.is.allowed';

        $this->assertFalse($this->helper->doesPermissionMatch('test.value.is.not-allowed', $perm));
        $this->assertTrue($this->helper->doesPermissionMatch('test.value.is.allowed', $perm));
        $this->assertTrue($this->helper->doesPermissionMatch('test.value.*', $perm));
        $this->assertTrue($this->helper->doesPermissionMatch('*', $perm));
    }
}
