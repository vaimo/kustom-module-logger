<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Logger\Cron;

use Klarna\Logger\Model\ResourceModel\Log\Collection as LogCollection;
use Klarna\Logger\Model\ResourceModel\Log\CollectionFactory as LogCollectionFactory;

/**
 * @api
 */
class CleanLogs
{
    public const SECONDSINDAY = 86400;

    public const LIFT_TIME = 90;

    /**
     * @var LogCollectionFactory
     */
    private LogCollectionFactory $logCollectionFactory;

    /**
     * @param LogCollectionFactory $logCollectionFactory
     * @codeCoverageIgnore
     */
    public function __construct(LogCollectionFactory $logCollectionFactory)
    {
        $this->logCollectionFactory = $logCollectionFactory;
    }

    /**
     * Clean expired logs (cron process).
     *
     * @return void
     */
    public function execute(): void
    {
        $logCollection = $this->getLogs();
        $logCollection->setPageSize(50);
        $lastPage = $logCollection->getSize() ? $logCollection->getLastPageNumber() : 0;

        for ($currentPage = $lastPage; $currentPage >= 1; $currentPage--) {
            $logCollection->setCurPage($currentPage);
            $logCollection->walk('delete');
            $logCollection->clear();
        }
    }

    /**
     * Gets logs.
     *
     * Log is considered expired if the created_at date
     * of the entry is greater than lifetime threshold
     *
     * @return LogCollection
     */
    private function getLogs(): LogCollection
    {
        $lifetime = $this->getLogLifeTime();
        $lifetime *= self::SECONDSINDAY;

        $logs = $this->logCollectionFactory->create();
        $logs->addFieldToFilter('created_at', ['to' => date("Y-m-d", time() - $lifetime)]);
        $logs->addFieldToSelect('log_id');

        return $logs;
    }

    /**
     * Its a public method so that it can be extended by other modules.
     *
     * @return int
     */
    public function getLogLifeTime(): int
    {
        return self::LIFT_TIME;
    }
}
