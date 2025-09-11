<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Logger\Test\Integration\Model;

use Klarna\Logger\Model\LogRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use DateTime;

/**
 * @internal
 */
class LogRepositoryTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var ResourceConnection
     */
    private $connection;
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var DateTime
     */
    private $dateTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->connection = $this->objectManager->get(ResourceConnection::class);
        $this->logRepository = $this->objectManager->get(LogRepository::class);
        $this->dateTime = $this->objectManager->get(DateTime::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSaveEntryWasSaved(): void
    {
        $expected = 'my_new_value';
        $connection = $this->connection->getConnection();
        $query = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, service) VALUES " .
            "('a', 'b', '1')";
        $connection->query($query);

        $query = "SELECT * FROM klarna_logs where service = 1";
        $result = $connection->fetchAll($query);
        $klarnaLog = $this->logRepository->getById($result[0]['log_id']);
        $klarnaLog->setIncrementId($expected);

        $this->logRepository->save($klarnaLog);

        $query = "SELECT * FROM klarna_logs where service = 1";
        $result = $connection->fetchAll($query);
        static::assertEquals($expected, $result[0]['increment_id']);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testDeleteEntryWasDeleted(): void
    {
        $connection = $this->connection->getConnection();
        $query = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, service) VALUES " .
            "('a', 'b', '1')";
        $connection->query($query);

        $query = "SELECT * FROM klarna_logs where service = 1";
        $result = $connection->fetchAll($query);
        $klarnaLog = $this->logRepository->getById($result[0]['log_id']);

        $this->logRepository->delete($klarnaLog);

        $query = "SELECT * FROM klarna_logs where service = 1";
        $result = $connection->fetchAll($query);
        static::assertEmpty($result);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testDeleteByIdEntryWasDeleted(): void
    {
        $connection = $this->connection->getConnection();
        $query = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, service) VALUES " .
            "('a', 'b', '1')";
        $connection->query($query);

        $query = "SELECT * FROM klarna_logs where service = 1";
        $result = $connection->fetchAll($query);
        $klarnaLog = $this->logRepository->getById($result[0]['log_id']);

        $this->logRepository->deleteById((string) $klarnaLog->getLogId());

        $query = "SELECT * FROM klarna_logs where service = 1";
        $result = $connection->fetchAll($query);
        static::assertEmpty($result);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testDeleteByIdEntryIsNotFound(): void
    {
        static::expectException(NoSuchEntityException::class);
        $this->logRepository->deleteById('999999');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetByIdEntryWasFound(): void
    {
        $expected = 'my_value';
        $connection = $this->connection->getConnection();
        $query = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, service) VALUES " .
            "('a', '" . $expected . "', '1')";
        $connection->query($query);

        $query = "SELECT * FROM klarna_logs where service = 1";
        $result = $connection->fetchAll($query);
        $klarnaLog = $this->logRepository->getById($result[0]['log_id']);

        $klarnaLog = $this->logRepository->getById((string) $klarnaLog->getLogId());
        static::assertEquals($expected, $klarnaLog->getIncrementId());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetByIdEntryWasNotFound(): void
    {
        static::expectException(NoSuchEntityException::class);
        $this->logRepository->getById('999999');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetLogsCreatedFromXDaysAgo()
    {
        $nineDaysAgo = $this->dateTime
            ->setTime(0, 0)
            ->modify('-9 days')
            ->format('Y-m-d H:i:s');
        $tenDaysAgo = $this->dateTime
            ->setTime(0, 0)
            ->modify('-10 days')
            ->format('Y-m-d H:i:s');
        $elevenDaysAgo = $this->dateTime
            ->setTime(0, 0)
            ->modify('-11 days')
            ->format('Y-m-d H:i:s');

        $connection = $this->connection->getConnection();
        $query1 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action) VALUES " .
            "('a', '1', '" . $elevenDaysAgo . "', 'Create Order')";
        $query2 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action) VALUES " .
            "('b', '2', '" . $nineDaysAgo . "', 'Create Order')";
        $query3 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action) VALUES " .
            "('c', '3', '" . $nineDaysAgo . "', 'Create Order')";
        $query4 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action) VALUES " .
            "('d', '4', '" . $nineDaysAgo . "', 'Create Order')";
        $connection->query($query1);
        $connection->query($query2);
        $connection->query($query3);
        $connection->query($query4);

        $query = "SELECT * FROM klarna_logs WHERE created_at >= '" . $tenDaysAgo ."' ORDER BY created_at DESC LIMIT 1000";
        $result = $connection->fetchAll($query);

        static::assertSame($result, $this->logRepository->getLogsCreatedFromXDaysAgo($nineDaysAgo, 1000));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetTotalCreateOrdersAttempts()
    {
        $tenDaysAgo = $this->dateTime
            ->setTime(0, 0)
            ->modify('-10 days')
            ->format('Y-m-d H:i:s');
        $nineDaysAgo = $this->dateTime
            ->setTime(0, 0)
            ->modify('-10 days')
            ->format('Y-m-d H:i:s');

        $connection = $this->connection->getConnection();
        $query1 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action) VALUES " .
            "('a', '1', '" . $nineDaysAgo . "', 'Create Order')";
        $query2 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action) VALUES " .
            "('b', '2', '" . $tenDaysAgo . "', 'Create Order')";
        $query3 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action) VALUES " .
            "('c', '3', '" . $tenDaysAgo . "', 'Create Order')";
        $query4 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action) VALUES " .
            "('d', '4', '" . $tenDaysAgo . "', 'Create Order')";
        $connection->query($query1);
        $connection->query($query2);
        $connection->query($query3);
        $connection->query($query4);

        $query = "SELECT COUNT(*) FROM klarna_logs where created_at >= '" . $tenDaysAgo ."'";
        $result = (int) $connection->fetchOne($query);

        static::assertSame($result, $this->logRepository->getTotalCreateOrdersAttempts($tenDaysAgo));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetTotalFailedOrdersAttempts()
    {
        $tenDaysAgo = $this->dateTime
            ->setTime(0, 0)
            ->modify('-10 days')
            ->format('Y-m-d H:i:s');

        $connection = $this->connection->getConnection();
        $query1 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action, status) VALUES " .
            "('a', '1', '" . $tenDaysAgo . "', 'Create Order', 403)";
        $query2 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action, status) VALUES " .
            "('b', '2', '" . $tenDaysAgo . "', 'Create Order', 403)";
        $query3 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action, status) VALUES " .
            "('c', '3', '" . $tenDaysAgo . "', 'Create Order', 200)";
        $query4 = "INSERT INTO " .
            "klarna_logs(klarna_id, increment_id, created_at, action, status) VALUES " .
            "('d', '4', '" . $tenDaysAgo . "', 'Create Order', 200)";
        $connection->query($query1);
        $connection->query($query2);
        $connection->query($query3);
        $connection->query($query4);

        $query = "SELECT COUNT(*) FROM klarna_logs where created_at >= '" . $tenDaysAgo ."'" .
            " AND status = 403 AND action = 'Create Order'";
        $result = (int) $connection->fetchOne($query);

        static::assertSame($result, $this->logRepository->getTotalFailedOrdersAttempts($tenDaysAgo));
    }
}