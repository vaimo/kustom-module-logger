<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

declare(strict_types=1);

namespace Klarna\Logger\Model;

use Klarna\Base\Model\RepositoryAbstract;
use Klarna\Logger\Api\Data\LogInterface;
use Klarna\Logger\Model\ResourceModel\Log\CollectionFactory as LogCollectionFactory;
use Klarna\Logger\Api\LogRepositoryInterface;
use Klarna\Logger\Model\ResourceModel\Log;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Klarna\Logger\Model\ResourceModel\Log\Collection;

/**
 * Repository class for the logs
 *
 * @internal
 */
class LogRepository extends RepositoryAbstract implements LogRepositoryInterface
{
    /**
     * @var LogFactory
     */
    private $logFactory;
    /**
     * @var LogCollectionFactory
     */
    private $logCollectionFactory;
    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @param Log                              $resourceModel
     * @param LogFactory                       $logFactory
     * @param LogCollectionFactory             $logCollectionFactory
     * @param SearchResultsInterfaceFactory    $searchResultsFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        Log $resourceModel,
        LogFactory $logFactory,
        LogCollectionFactory $logCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        parent::__construct($resourceModel, $logFactory);
        $this->logFactory           = $logFactory;
        $this->logCollectionFactory = $logCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(string $id): LogInterface
    {
        $log = $this->getById($id);
        return $this->delete($log);
    }

    /**
     * @inheritdoc
     */
    public function getById(string $logId): LogInterface
    {
        return $this->getByKeyValuePair('log_id', $logId);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $collection = $this->getCollection($searchCriteria);

        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * Getting back the collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return Collection
     */
    private function getCollection(SearchCriteriaInterface $searchCriteria): Collection
    {
        $collection = $this->logCollectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        return $collection;
    }

    /**
     * Get logs that were created X days ago and limit the result. Date format: "Y-m-d H:i:s"
     *
     * @param string $daysAgo
     * @param int $pageSize
     * @return array[]
     */
    public function getLogsCreatedFromXDaysAgo(string $daysAgo, int $pageSize): array
    {
        $data = $this->createEmptyCollection()
            ->setPageSize($pageSize)
            ->addFieldToFilter('created_at', ['gteq' => $daysAgo])
            ->setOrder('created_at', 'DESC')
            ->getItems();

        $result = [];
        foreach ($data as $item) {
            $result[] = $item->getData();
        }
        return $result;
    }

    /**
     * Counts the total number of created order attempts since X days ago. Date format: "Y-m-d H:i:s"
     *
     * @param string $xDaysAgo
     * @return int
     */
    public function getTotalCreateOrdersAttempts(string $xDaysAgo): int
    {
        $collection = $this->createEmptyCollection();
        $collection->addFieldToFilter('action', 'Create Order')
            ->addFieldToFilter('created_at', ['gteq' => $xDaysAgo]);
        return $collection->getSize();
    }

    /**
     * Counts the total number of failed created order attempts since X days ago. Date format: "Y-m-d H:i:s"
     *
     * @param string $xDaysAgo
     * @return int
     */
    public function getTotalFailedOrdersAttempts(string $xDaysAgo): int
    {
        $collection = $this->createEmptyCollection();
        $collection->addFieldToFilter('action', 'Create Order')
            ->addFieldToFilter('status', 403)
            ->addFieldToFilter('created_at', ['gteq' => $xDaysAgo]);
        return $collection->getSize();
    }

    /**
     * Returns an empty collection
     *
     * @return Collection
     */
    private function createEmptyCollection(): Collection
    {
        return $this->logCollectionFactory->create();
    }
}
