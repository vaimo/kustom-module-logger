<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Logger\Test\Unit\Model;

use Klarna\Logger\Model\Cleanser;
use Klarna\Base\Test\Unit\Mock\TestCase;

/**
 * @coversDefaultClass \Klarna\Logger\Model\Cleanser
 */
class CleanserTest extends TestCase
{

    private Cleanser $cleanser;

    public function testCleanInputtingEmptyArrayReturnsEmptyArray(): void
    {
        static::assertEquals([], $this->cleanser->clean([]));
    }

    public function testCleanNothingIsCleanedSinceNoKeyMatched(): void
    {
        $expected = [
            'aa' => 'bb',
            'c' => 'd',
            'e' => 'f'
        ];
        static::assertEquals($expected, $this->cleanser->clean($expected));
    }

    public function testCleanEverythingIsCleanedSinceAllKeysMatched(): void
    {
        $input = [
            'street_address' => 'd',
            'family_name' => 'f'
        ];
        $expected = [
            'street_address' => '** REMOVED **',
            'family_name' => '** REMOVED **'
        ];
        static::assertEquals($expected, $this->cleanser->clean($input));
    }

    public function testCleanSubsetIsCleanedSinceSubsetOfKeysMatched(): void
    {
        $input = [
            'aa' => 'd',
            'family_name' => 'f'
        ];
        $expected = [
            'aa' => 'd',
            'family_name' => '** REMOVED **'
        ];
        static::assertEquals($expected, $this->cleanser->clean($input));
    }

    protected function setUp(): void
    {
        $this->cleanser = parent::setUpMocks(Cleanser::class);
    }
}
