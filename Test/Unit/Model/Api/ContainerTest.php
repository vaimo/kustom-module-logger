<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Logger\Test\Unit\Model\Unit\Logger\Api;

use Klarna\Logger\Model\Api\Container;
use Klarna\Base\Test\Unit\Mock\TestCase;

/**
 * @coversDefaultClass \Klarna\Logger\Model\Api\Container
 */
class ContainerTest extends TestCase
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @covers ::getUrl
     */
    public function testGetUrl(): void
    {
        $expected = 'my_url';
        $this->container->setUrl($expected);
        static::assertEquals($expected, $this->container->getUrl());
    }

    /**
     * @covers ::getMethod
     */
    public function testGetMethod(): void
    {
        $expected = 'my_method';
        $this->container->setMethod($expected);
        static::assertEquals($expected, $this->container->getMethod());
    }

    /**
     * @covers ::getAction
     */
    public function testGetAction(): void
    {
        $expected = 'my_action';
        $this->container->setAction($expected);
        static::assertEquals($expected, $this->container->getAction());
    }

    /**
     * @covers ::getRequest
     */
    public function testGetRequest(): void
    {
        $expected = ['key' => 'value'];
        $this->container->setRequest($expected);
        static::assertEquals($expected, $this->container->getRequest());
    }

    /**
     * @covers ::getResponse
     */
    public function testGetResponse(): void
    {
        $expected = ['key' => 'value'];
        $this->container->setResponse($expected);
        static::assertEquals($expected, $this->container->getResponse());
    }

    /**
     * @covers ::getKlarnaId
     */
    public function testGetKlarnaId(): void
    {
        $expected = 'klarna_id';
        $this->container->setKlarnaId($expected);
        static::assertEquals($expected, $this->container->getKlarnaId());
    }

    /**
     * @covers ::getService
     */
    public function testGetService(): void
    {
        $expected = 'my_service';
        $this->container->setService($expected);
        static::assertEquals($expected, $this->container->getService());
    }

    /**
     * @covers ::getIncrementId
     */
    public function testGetIncrementId(): void
    {
        $expected = 'my_id';
        $this->container->setIncrementId($expected);
        static::assertEquals($expected, $this->container->getIncrementId());
    }

    protected function setUp(): void
    {
        $this->container = parent::setUpMocks(Container::class);
    }
}
