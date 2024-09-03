<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Entity schema.
 */
interface EntitySchema extends ObjectSchema
{
    public function type(): string;

    public function idProperty(): Property;
}
