<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Logger\Test\Unit\Cron;

use Klarna\Logger\Cron\UpdateApiLog;
use PHPUnit\Framework\MockObject\MockObject;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Klarna\Logger\Model\Log;
use Klarna\Logger\Model\ResourceModel\Log\Collection as LogCollection;
use Magento\Framework\DB\Select;

/**
 * @coversDefaultClass \Klarna\Logger\Cron\UpdateApiLog
 */
class UpdateApiLogTest extends TestCase
{
    /**
     * @var UpdateApiLog
     */
    private $updateApiLog;

    /**
     * @covers ::execute()
     */
    public function testExecuteUpdateModel(): void
    {
        $logCollection = $this->mockFactory->create(LogCollection::class);
        $select = $this->mockFactory->create(Select::class);
        $logCollection->method('getSelect')
            ->willReturn($select);
        $log = $this->mockFactory->create(Log::class);
        $log->method('getIncrementId')
            ->willReturn('100000000');
        $log->method('getKlarnaId')
            ->willReturn('e2b130e9-fc4f-49ed-97be-7468b539cd77');
        $logCollection->method('getItems')
            ->willReturn([$log]);
        $this->dependencyMocks['logCollectionFactory']->method('create')
            ->willReturn($logCollection);
        $this->dependencyMocks['logRepository']->expects(static::once())
            ->method('save');

        $this->updateApiLog->execute();
    }

    protected function setUp(): void
    {
        $this->updateApiLog = parent::setUpMocks(UpdateApiLog::class);
    }
}
