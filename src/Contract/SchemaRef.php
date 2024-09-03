<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * A reference to schema.
 */
interface SchemaRef
{
    /**
     * Type of schema being referenced.
     *
     * @psalm-return class-string<ObjectSchema>
     */
    public function type(): string;

    /**
     * Schema source class name.
     *
     * @psalm-return class-string
     */
    public function source(): string;

    /**
     * Referenced schema.
     */
    public function unwrap(): ObjectSchema;
}
