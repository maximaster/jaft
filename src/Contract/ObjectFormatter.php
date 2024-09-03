<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Formats an object into a primitive supported by OpenAPI.
 */
interface ObjectFormatter
{
    public function supports(object $object): bool;

    /**
     * @return int|float|string|array
     */
    public function format(object $object);
}
