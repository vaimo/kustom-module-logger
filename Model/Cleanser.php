<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Logger\Model;

/**
 * @internal
 */
class Cleanser
{
    /**
     * @var array
     */
    private array $sensitiveKeys = [
        'date_of_birth',
        'given_name',
        'gender',
        'family_name',
        'email',
        'street_address',
        'phone',
        'title',
        'postal_code',
        'city',
        'phone'
    ];
    /**
     * @var string
     */
    public string $replacement = '** REMOVED **';

    /**
     * Replace sensitive data with a replacement
     *
     * @param array $input
     * @return array
     */
    public function clean(array $input): array
    {
        array_walk_recursive(
            $input,
            function (&$value, $key) {
                if (in_array($key, $this->sensitiveKeys, true)) {
                    $value = $this->replacement;
                }
            }
        );

        return $input;
    }
}
