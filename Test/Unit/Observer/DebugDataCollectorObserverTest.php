<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Logger\Test\Unit\Observer;

use DateTime;
use Klarna\Base\Helper\Debug\DebugDataObject;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Klarna\Logger\Observer\DebugDataCollectorObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;

/**
 * @internal
 */
class DebugDataCollectorObserverTest extends TestCase
{
    /**
     * @var DebugDataCollectorObserver
     */
    private $debugDataCollectorObserver;

    /**
     * @var string
     */
    private string $tenDaysAgo;

    /**
     * @var DebugDataObject
     */
    private  $debugDataObject;

    /**
     * @var Observer
     */
    private  $observer;

    /**
     * @var Event
     */
    private  $event;

    protected function setUp(): void
    {
        $this->debugDataCollectorObserver = parent::setUpMocks(DebugDataCollectorObserver::class);
        $this->debugDataObject = $this->createSingleMock(DebugDataObject::class);
        $this->observer = $this->createSingleMock(Observer::class);
        $this->event = $this->createSingleMock(Event::class, [], ['getDebugDataObject']);

        $dateTime = new DateTime();

        $this->tenDaysAgo = $dateTime->setTime(0, 0)
            ->modify('-10 days')
            ->format('Y-m-d H:i:s');
    }

    private function setupExpectationsForTests(array $data): void
    {
        $this->dependencyMocks['dateTime']
            ->expects($this->once())
            ->method('setTime')
            ->with(0, 0)
            ->willReturnSelf();
        $this->dependencyMocks['dateTime']
            ->expects($this->once())
            ->method('modify')
            ->with('-10 days')
            ->willReturnSelf();
        $this->dependencyMocks['dateTime']
            ->expects($this->once())
            ->method('format')
            ->with('Y-m-d H:i:s')
            ->willReturn($this->tenDaysAgo);

        $this->debugDataObject->expects(static::once())
            ->method('addData');
        $this->event->expects(static::once())
            ->method('getDebugDataObject')
            ->willReturn($this->debugDataObject);
        $this->observer->expects(static::once())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->dependencyMocks['logRepository']->method('getLogsCreatedFromXDaysAgo')
            ->with($this->tenDaysAgo, 1000)
            ->willReturn($data);
    }

    public function testExecutionAddsStringifiedEmptyTableDataToDebugDataObject(): void
    {
        $data = [];
        $this->setupExpectationsForTests($data);

        $this->dependencyMocks['stringifyDbTableData']->expects($this->once())
            ->method('getStringData')
            ->with($data)
            ->willReturn('[]');

        $this->debugDataCollectorObserver->execute($this->observer);
    }

    public function testExecutionAddsStringifiedTableDataToDebugDataObject(): void
    {
        $data = [
            ['id' => 1, 'created_at' => $this->tenDaysAgo, 'message' => 'test message'],
            ['id' => 2, 'created_at' => $this->tenDaysAgo, 'message' => 'test message 2'],
        ];
        $this->setupExpectationsForTests($data);

        $this->dependencyMocks['stringifyDbTableData']->expects($this->once())
            ->method('getStringData')
            ->with($data)
            ->willReturn('[
                {"id": 1, "created_at": "' . $this->tenDaysAgo . '", "message": "test message"},
                {"id": 2, "created_at": "' . $this->tenDaysAgo . '", "message": "test message 2"}
            ]');

        $this->debugDataCollectorObserver->execute($this->observer);
    }
}
