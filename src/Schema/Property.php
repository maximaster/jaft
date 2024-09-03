<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Schema;

use Maximaster\Jaft\Contract\EntitySchema;
use Maximaster\Jaft\Contract\ObjectSchema;
use Maximaster\Jaft\Contract\Property as PropertyInterface;
use Maximaster\Jaft\Contract\SchemaRef;
use Maximaster\Jaft\Exception\NonObjectPropertyException;

/**
 * Entity property.
 *
 * @psalm-immutable
 */
class Property implements PropertyInterface
{
    public string $name;
    public string $type;
    public bool $iterable;
    public ?SchemaRef $ref;

    public function __construct(string $name, string $type, bool $iterable, ?SchemaRef $ref)
    {
        $this->name = $name;
        $this->type = $type;
        $this->iterable = $iterable;
        $this->ref = $ref;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function iterable(): bool
    {
        return $this->iterable;
    }

    /**
     * @return EntitySchema
     *
     * @throws NonObjectPropertyException
     */
    public function schema(): ObjectSchema
    {
        return $this->ref()->unwrap();
    }

    public function hasSchema(): bool
    {
        return $this->ref !== null;
    }

    /**
     * @throws NonObjectPropertyException
     *
     * @psalm-return class-string<ObjectSchema>
     */
    public function schemaType(): string
    {
        return $this->ref()->type();
    }

    /**
     * @throws NonObjectPropertyException
     */
    public function schemaSource(): string
    {
        return $this->ref()->source();
    }

    /**
     * @throws NonObjectPropertyException
     */
    private function ref(): SchemaRef
    {
        if ($this->ref === null) {
            throw new NonObjectPropertyException(sprintf("'%s' doesn't have schema attached", $this->name));
        }

        return $this->ref;
    }
}
