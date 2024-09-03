<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Converts schema to something else.
 */
interface SchemaConverter
{
    public function convert(EntitySchema $schema);
}
