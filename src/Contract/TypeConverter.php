<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Type converter.
 */
interface TypeConverter
{
    /**
     * Converts some external type to Open API type.
     *
     * @param string $externalType some external type (PHP type for example)
     *
     * @return string Open API type
     */
    public function convertType(string $externalType): string;
}
