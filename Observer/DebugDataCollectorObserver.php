<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Logger\Observer;

use DateTime;
use Klarna\Base\Helper\Debug\DebugDataObject;
use Klarna\Base\Helper\Debug\StringifyDbTableData;
use Klarna\Logger\Model\LogRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @internal
 */
class DebugDataCollectorObserver implements ObserverInterface
{
    /**
     * @var StringifyDbTableData
     */
    private StringifyDbTableData $stringifyDbTableData;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @param StringifyDbTableData $stringifyDbTableData
     * @param DateTime $dateTime
     * @param LogRepository $logRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        StringifyDbTableData $stringifyDbTableData,
        DateTime $dateTime,
        LogRepository $logRepository
    ) {
        $this->stringifyDbTableData = $stringifyDbTableData;
        $this->dateTime = $dateTime;
        $this->logRepository = $logRepository;
    }

    /**
     * Collects data from the database and adds it to the debug data object
     *
     * @param Observer $observer
     * @return void
     * @throws \DateMalformedStringException
     */
    public function execute(Observer $observer): void
    {
        $this->setDataToDataObject($observer->getEvent()->getDebugDataObject());
    }

    /**
     * Adds data from the database to the debug data object
     *
     * @param DebugDataObject $dataObject
     * @return void
     * @throws \DateMalformedStringException
     */
    protected function setDataToDataObject(DebugDataObject $dataObject): void
    {
        $tenDaysAgo = $this->dateTime
            ->setTime(0, 0)
            ->modify('-10 days')
            ->format('Y-m-d H:i:s');

        $logData = $this->logRepository->getLogsCreatedFromXDaysAgo($tenDaysAgo, 1000);
        $klarnaLogTableData = $this->stringifyDbTableData->getStringData($logData);

        $dataObject->addData('klarna_logger', $klarnaLogTableData);
    }
}
