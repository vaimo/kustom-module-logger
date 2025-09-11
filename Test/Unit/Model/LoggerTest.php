<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Logger\Test\Unit\Model;

use Klarna\Logger\Model\Logger;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Magento\Store\Api\Data\StoreInterface;
use Klarna\Logger\Model\Handlers\File;

/**
 * @coversDefaultClass \Klarna\Logger\Model\Logger
 */
class LoggerTest extends TestCase
{
    /**
     * @var Logger
     */
    private Logger $logger;
    /**
     * @var StoreInterface
     */
    private StoreInterface $storeOne;
    /**
     * @var StoreInterface
     */
    private StoreInterface $storeTwo;
    /**
     * @var File
     */
    private File $handlerItem;
    /**
     * @var Logger
     */
    private Logger $loggerWithHandler;
    /**
     * @var array
     */
    private array $dependencyMocksWithHandler;

    public function testLogNoStoreSetInClassAndStoreManagerReturnsNullAsStoreImpliesNothingIsLogged(): void
    {
        $this->handlerItem->expects(static::never())
            ->method('handle');
        $this->logger->log('a', 'my_message');
    }

    public function testLogStoreSetInClassButLoggingIsDisabledImpliesNothingIsLogged(): void
    {
        $this->logger->setStore($this->storeOne);

        $this->handlerItem->expects(static::never())
            ->method('handle');
        $this->logger->log('error', 'my_message');
    }

    public function testLogNoStoreSetInClassButStoreManagerReturnsStoreButLoggingIsDisabledImpliesNothingIsLogged(): void
    {
        $this->dependencyMocks['storeManager']->method('getStore')
            ->willReturn($this->storeOne);

        $this->handlerItem->expects(static::never())
            ->method('handle');
        $this->logger->log('error', 'my_message');
    }

    public function testLogStoreSetInClassAndLoggingIsEnabledAndMessageIsExceptionButNoHandlerConfiguredImpliesNothingIsLogged(): void
    {
        $this->logger->setStore($this->storeOne);

        $this->dependencyMocks['loggerConfigurations']->method('isEnabled')
            ->willReturn(true);
        $this->handlerItem->expects(static::never())
            ->method('handle');
        $this->logger->log('a', new \Exception('my_exception'));
    }

    public function testLogNoStoreSetInClassButStoreManagerReturnsStoreAndLoggingIsEnabledAndMessageIsStringButNoHandlerConfiguredImpliesNothingIsLogged(): void
    {
        $this->logger->setStore($this->storeOne);

        $this->dependencyMocks['loggerConfigurations']->method('isEnabled')
            ->willReturn(true);
        $this->handlerItem->expects(static::never())
            ->method('handle');
        $this->logger->log('error', 'my_message');
    }

    public function testLogNoStoreSetInClassButStoreManagerReturnsStoreAndLoggingIsEnabledAndMessageIsExceptionAndHandlerIsConfiguredImpliesCalledLoggingMethod(): void
    {
        $this->loggerWithHandler->setStore($this->storeOne);

        $this->dependencyMocksWithHandler['loggerConfigurations']->method('isEnabled')
            ->willReturn(true);
        $this->handlerItem->expects(static::once())
            ->method('handle');
        $this->loggerWithHandler->log('a', new \Exception('my_exception'));
    }

    public function testLogStoreSetInClassAndLoggingIsEnabledAndMessageIsStringAndHandlerIsConfiguredImpliesCalledLoggingMethod(): void
    {
        $this->loggerWithHandler->setStore($this->storeOne);

        $this->dependencyMocksWithHandler['loggerConfigurations']->method('isEnabled')
            ->willReturn(true);
        $this->handlerItem->expects(static::once())
            ->method('handle');
        $this->loggerWithHandler->log('error', 'my_message');
    }

    public function testLogStoreSetInClassImpliesUsingStoreForLoggingConfigurationFlagCheck(): void
    {
        $this->logger->setStore($this->storeOne);

        $this->dependencyMocks['loggerConfigurations']->expects(static::once())
            ->method('isEnabled')
            ->with($this->storeOne);
        $this->logger->log('error', 'my_message');
    }

    public function testLogStoreSetInClassAndAnotherStoreReturnedFromStoreManagerImpliesUsingStoreInClassForLoggingConfigurationFlagCheck(): void
    {
        $this->logger->setStore($this->storeOne);

        $this->dependencyMocks['storeManager']->method('getStore')
            ->willReturn($this->storeTwo);
        $this->dependencyMocks['loggerConfigurations']->expects(static::once())
            ->method('isEnabled')
            ->with($this->storeOne);
        $this->logger->log('error', 'my_message');
    }

    public function testLogStoreNotSetInClassAndStoreReturnedFromStoreManagerImpliesUsingStoreFromStoreManagerForLoggingConfigurationFlagCheck(): void
    {
        $this->dependencyMocks['storeManager']->method('getStore')
            ->willReturn($this->storeOne);
        $this->dependencyMocks['loggerConfigurations']->expects(static::once())
            ->method('isEnabled')
            ->with($this->storeOne);
        $this->logger->log('error', 'my_message');
    }

    protected function setUp(): void
    {
        $this->logger = parent::setUpMocks(Logger::class);

        $this->storeOne = $this->mockFactory->create(StoreInterface::class);
        $this->storeTwo = $this->mockFactory->create(StoreInterface::class);
        $this->handlerItem = $this->mockFactory->create(File::class);

        $this->loggerWithHandler = $this->objectFactory->create(Logger::class, [], ['handlers' => [$this->handlerItem]]);
        $this->dependencyMocksWithHandler = $this->objectFactory->getDependencyMocks();
    }
}