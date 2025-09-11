<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Logger\Test\Unit\Model\Logger\Api;

use Klarna\Logger\Model\Api\Container;
use Klarna\Logger\Model\Api\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Klarna\Base\Test\Unit\Mock\TestCase;

/**
 * @coversDefaultClass \Klarna\Logger\Model\Api\Logger
 */
class LoggerTest extends TestCase
{
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Container
     */
    private $container;

    /**
     * @doesNotPerformAssertions
     * @covers ::logContainer
     */
    public function testLogContainerAddEntry(): void
    {
        $this->logger->logContainer($this->container);
    }

    protected function setUp(): void
    {
        $this->logger = parent::setUpMocks(Logger::class);
        $this->container = $this->mockFactory->create(Container::class);
    }
}
