<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Logger\Test\Unit\Model\Api;

use Klarna\Logger\Model\Api\Container;
use Klarna\Logger\Model\Api\Create;
use Klarna\Base\Test\Unit\Mock\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Klarna\Logger\Model\Log;

/**
 * @coversDefaultClass \Klarna\Logger\Model\Logger\Api\Create
 */
class CreateTest extends TestCase
{
    /**
     * @var Create
     */
    private $createLogger;
    /**
     * @var Container
     */
    private $container;

    /**
     * @doesNotPerformAssertions
     */
    public function testAddEntryDataIsAnonymized(): void
    {
        $request = [
            'billing_address' => [
                'lastname' => 'my last name'
            ],
            'shipping_address' => [
                'lastname' => 'my last name'
            ]
        ];
        $response = array_merge(['response_status_code' => 1], $request);

        $this->container->method('getRequest')
            ->willReturn($request);
        $this->container->method('getResponse')
            ->willReturn($response);

        $logModel = $this->mockFactory->create(Log::class);
        $logModel->method('setRequest')
            ->with(json_encode($request));
        $logModel->method('setResponse')
            ->with(json_encode($response));

        $this->dependencyMocks['json']->method('serialize')
            ->will($this->returnCallback(function ($value) {
                return json_encode($value);
            }));
        $this->dependencyMocks['cleanser']->method('clean')
            ->will($this->returnCallback(function ($value) {
                return $value;
            }));

        $this->dependencyMocks['logFactory']->method('create')
            ->willReturn($logModel);
        $this->createLogger->addEntry($this->container);
    }

    protected function setUp(): void
    {
        $this->createLogger = parent::setUpMocks(Create::class);
        $this->container = $this->mockFactory->create(Container::class);
    }
}
