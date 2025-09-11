<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Logger\Test\Unit\Model\System\Config\Source;

use Klarna\Logger\Model\System\Config\Source\Method;
use Klarna\Base\Test\Unit\Mock\TestCase;

/**
 * @coversDefaultClass  \Klarna\Logger\Model\System\Config\Source\Method
 */
class MethodTest extends TestCase
{
    /**
     * @var Method
     */
    private Method $model;

    public function testToOptionArrayReturnsNotEmptyArray(): void
    {
        static::assertNotEmpty($this->model->toOptionArray());
    }

    protected function setUp(): void
    {
        $this->model = parent::setUpMocks(Method::class);
    }
}
