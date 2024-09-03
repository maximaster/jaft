<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

use Maximaster\Jaft\Exception\NonObjectPropertyException;

/**
 * Property schema.
 */
interface Property
{
    /**
     * Name of the property.
     */
    public function name(): string;

    /**
     * Property type for OpenAPI.
     */
    public function type(): string;

    /**
     * Is is iterable or not.
     */
    public function iterable(): bool;

    /**
     * Property type schema. Only for objects.
     *
     * @return ObjectSchema|null
     *
     * @throws NonObjectPropertyException
     */
    public function schema(): ObjectSchema;

    /**
     * Does it have schema or not?
     */
    public function hasSchema(): bool;

    /**
     * Schema type.
     *
     * @psalm-return class-string<ObjectSchema>
     */
    public function schemaType(): string;

    /**
     * Schema source class.
     *
     * @psalm-return class-string
     */
    public function schemaSource(): string;
}
