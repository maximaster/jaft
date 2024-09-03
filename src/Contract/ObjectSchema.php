<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Object schema.
 */
interface ObjectSchema
{
    /**
     * @return string source class of schema
     */
    public function source(): string;

    public function property(string $name): Property;

    /**
     * All entity relationships. Name can be nested (e.g. created.by).
     *
     * @return string[]
     */
    public function relationships(): array;

    /**
     * All entity attribute names. Name can be nested (e.g. created.by).
     *
     * @return string[]
     */
    public function attributes(): array;
}
