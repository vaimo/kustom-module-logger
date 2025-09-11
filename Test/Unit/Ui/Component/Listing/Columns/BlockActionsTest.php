<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Logger\Test\Unit\Ui\Component\Listing\Columns;

use Klarna\Logger\Ui\Component\Listing\Columns\BlockActions;
use Klarna\Base\Test\Unit\Mock\TestCase;

/**
 * @coversDefaultClass \Klarna\Logger\Ui\Component\Listing\Columns\Status
 */
class BlockActionsTest extends TestCase
{
    /**
     * @var BlockActions
     */
    private $blockActions;

    /**
     * Test button for view will be added.
     *
     * @covers ::prepareDataSource
     */
    public function testRowAction()
    {
        $dataSource['data']['items'][0]['log_id'] = 1;
        $this->blockActions->setData('name', 'button');
        $actual = $this->blockActions->prepareDataSource($dataSource);
        $this::assertArrayHasKey('view', $actual['data']['items'][0]['button']);
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->blockActions = parent::setUpMocks(BlockActions::class);
    }
}
