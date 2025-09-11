<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Logger\Test\Unit\Model;

use Klarna\Logger\Model\Log;
use Klarna\Logger\Model\LogRepository;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Klarna\Logger\Model\ResourceModel\Log\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\DataObject;

/**
 * @coversDefaultClass \Klarna\Logger\Model\LogRepository
 */
class LogRepositoryTest extends TestCase
{
    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @covers ::save()
     */
    public function testSave(): void
    {
        $actual = $this->logRepository->save($this->log);
        static::assertInstanceOf(Log::class, $actual);
    }

    /**
     * @covers ::delete()
     */
    public function testDelete(): void
    {
        $actual = $this->logRepository->delete($this->log);
        static::assertInstanceOf(Log::class, $actual);
    }

    /**
     * @covers ::deleteById()
     */
    public function testDeleteById(): void
    {
        $this->log->method('getId')
            ->willReturn('1');
        $this->dependencyMocks['logFactory']->method('create')
            ->willReturn($this->log);
        $actual = $this->logRepository->deleteById('1');
        static::assertInstanceOf(Log::class, $actual);
    }

    /**
     * @covers ::getById()
     */
    public function testGetById(): void
    {
        $this->log->method('getId')->willReturn('1');
        $this->dependencyMocks['logFactory']->method('create')
            ->willReturn($this->log);
        $actual = $this->logRepository->getById('1');
        static::assertInstanceOf(Log::class, $actual);
    }

    /**
     * @covers ::getList()
     */
    public function testGetListWithEmptyFilterGroups(): void
    {
        $searchResultInstance = $this->createSingleMock(SearchResults::class);
        $this->dependencyMocks['searchResultsFactory']->method('create')
            ->willReturn($searchResultInstance);

        $this->collection->method('getItems')
            ->willReturn([]);
        $this->dependencyMocks['logCollectionFactory']->method('create')
            ->willReturn($this->collection);

        $searchCriteria = $this->createSingleMock(SearchCriteria::class);
        $searchCriteria->method('getFilterGroups')
            ->willReturn([]);
        $result = $this->logRepository->getList($searchCriteria);

        static::assertSame($searchResultInstance, $result);
    }

    /**
     * @covers::getLogsCreatedFromXDaysAgo()
     */
    public function testGetLogsCreatedFromXDaysAgo()
    {
        $daysAgo = '2024-12-05 00:00:00';
        $pageSize = 1;
        $expected = [
            [
                'id' => 1,
                'created_at' => '2024-12-06 00:00:00',
                'message' => 'test message'
            ],
            [
                'id' => 2,
                'created_at' => '2021-12-06 00:00:00',
                'message' => 'test message'
            ],
            [
                'id' => 3,
                'created_at' => '2021-12-06 00:00:00',
                'message' => 'test message'
            ]
        ];

        $this->dependencyMocks['logCollectionFactory']->expects(static::once())
            ->method('create')
            ->willReturn($this->collection);
        $this->collection->expects(static::once())
            ->method('setPageSize')
            ->with($pageSize)
            ->willReturn($this->collection);
        $this->collection->expects(static::once())
            ->method('addFieldToFilter')
            ->with('created_at', ['gteq' => $daysAgo])
            ->willReturn($this->collection);
        $this->collection->expects(static::once())
            ->method('setOrder')
            ->with('created_at', 'DESC')
            ->willReturn($this->collection);
        $this->collection->expects(static::once())
            ->method('getItems')
            ->willReturn([
                $this->dataObject,
                $this->dataObject,
                $this->dataObject
            ]);
        $this->dataObject->expects(static::exactly(3))
            ->method('getData')
            ->willReturnOnConsecutiveCalls($expected[0], $expected[1], $expected[2]);

        $result = $this->logRepository->getLogsCreatedFromXDaysAgo($daysAgo, $pageSize);

        static::assertSame($expected, $result);
    }

    /**
     * @covers::getLogsCreatedFromXDaysAgo()
     */
    public function testGetLogsCreatedFromXDaysAgoNoResults()
    {
        $daysAgo = '2024-12-05 00:00:00';
        $pageSize = 1;
        $expected = [];

        $this->dependencyMocks['logCollectionFactory']->expects(static::once())
            ->method('create')
            ->willReturn($this->collection);
        $this->collection->expects(static::once())
            ->method('setPageSize')
            ->with($pageSize)
            ->willReturn($this->collection);
        $this->collection->expects(static::once())
            ->method('addFieldToFilter')
            ->with('created_at', ['gteq' => $daysAgo])
            ->willReturn($this->collection);
        $this->collection->expects(static::once())
            ->method('setOrder')
            ->with('created_at', 'DESC')
            ->willReturn($this->collection);
        $this->collection->expects(static::once())
            ->method('getItems')
            ->willReturn([]);

        $result = $this->logRepository->getLogsCreatedFromXDaysAgo($daysAgo, $pageSize);

        static::assertSame($expected, $result);
    }

    /**
     * @covers::getTotalCreateOrdersAttempts()
     */
    public function testGetTotalCreateOrdersAttempts()
    {
        $daysAgo = '2024-12-05 00:00:00';
        $expected = [
            [
                'id' => 1,
                'created_at' => '2024-12-06 00:00:00',
                'action' => 'Create Order'
            ],
            [
                'id' => 2,
                'created_at' => '2021-12-06 00:00:00',
                'action' => 'Create Order'
            ]
        ];

        $this->dependencyMocks['logCollectionFactory']->expects(static::once())
            ->method('create')
            ->willReturn($this->collection);
        $this->collection->expects(static::exactly(2))
            ->method('addFieldToFilter')
            ->willReturnCallback(fn($filter, $value) =>
                match([$filter, $value]) {
                    ['action', 'Create Order'] => $this->collection,
                    ['created_at', ['gteq' => $daysAgo]] => $this->collection
                }
            );
        $this->collection->expects(static::once())
            ->method('getSize')
            ->willReturn(count($expected));

        $result = $this->logRepository->getTotalCreateOrdersAttempts($daysAgo);

        static::assertSame(count($expected), $result);
    }

    /**
     * @covers::getTotalCreateOrdersAttempts()
     */
    public function testGetTotalCreateOrdersAttemptsNoCreateOrdersFound()
    {
        $daysAgo = '2024-12-05 00:00:00';

        $this->dependencyMocks['logCollectionFactory']->expects(static::once())
            ->method('create')
            ->willReturn($this->collection);
        $this->collection->expects(static::exactly(2))
            ->method('addFieldToFilter')
            ->willReturnCallback(fn($filter, $value) =>
                match([$filter, $value]) {
                    ['action', 'Create Order'] => $this->collection,
                    ['created_at', ['gteq' => $daysAgo]] => $this->collection
                }
            );
        $this->collection->expects(static::once())
            ->method('getSize')
            ->willReturn(0);

        $result = $this->logRepository->getTotalCreateOrdersAttempts($daysAgo);

        static::assertSame(0, $result);
    }

    /**
     * @covers::getTotalFailedOrdersAttempts()
     */
    public function testGetTotalFailedOrdersAttempts()
    {
        $daysAgo = '2024-12-05 00:00:00';
        $expected = [
            [
                'id' => 1,
                'created_at' => '2024-12-06 00:00:00',
                'action' => 'Create Order',
                'status' => 403
            ],
            [
                'id' => 2,
                'created_at' => '2021-12-06 00:00:00',
                'action' => 'Create Order',
                'status' => 403
            ]
        ];

        $this->dependencyMocks['logCollectionFactory']->expects(static::once())
            ->method('create')
            ->willReturn($this->collection);
        $this->collection->expects(static::exactly(3))
            ->method('addFieldToFilter')
            ->willReturnCallback(fn($filter, $value) =>
                match([$filter, $value]) {
                    ['action', 'Create Order'] => $this->collection,
                    ['status', 403] => $this->collection,
                    ['created_at', ['gteq' => $daysAgo]] => $this->collection
                }
            );
        $this->collection->expects(static::once())
            ->method('getSize')
            ->willReturn(count($expected));

        $result = $this->logRepository->getTotalFailedOrdersAttempts($daysAgo);

        static::assertSame(count($expected), $result);
    }

    /**
     * @covers::getTotalFailedOrdersAttempts()
     */
    public function testGetTotalFailedOrdersAttemptsCreateOrdersFound()
    {
        $daysAgo = '2024-12-05 00:00:00';

        $this->dependencyMocks['logCollectionFactory']->expects(static::once())
            ->method('create')
            ->willReturn($this->collection);
        $this->collection->expects(static::exactly(3))
            ->method('addFieldToFilter')
            ->willReturnCallback(fn($filter, $value) =>
                match([$filter, $value]) {
                    ['action', 'Create Order'] => $this->collection,
                    ['status', 403] => $this->collection,
                    ['created_at', ['gteq' => $daysAgo]] => $this->collection
                }
            );
        $this->collection->expects(static::once())
            ->method('getSize')
            ->willReturn(0);

        $result = $this->logRepository->getTotalFailedOrdersAttempts($daysAgo);

        static::assertSame(0, $result);
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->logRepository = parent::setUpMocks(LogRepository::class);
        $this->log = $this->createSingleMock(Log::class);
        $this->collection = $this->createSingleMock(Collection::class);
        $this->dataObject = $this->createSingleMock(DataObject::class);
    }
}
