<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Schema;

use Maximaster\Jaft\Contract\EntitySchema;
use Maximaster\Jaft\Contract\ObjectSchema as ObjectSchemaInterface;
use Maximaster\Jaft\Contract\Property;
use Maximaster\Jaft\Exception\UnknownPropertyException;

/**
 * Schema of an object.
 */
class ObjectSchema implements ObjectSchemaInterface
{
    /**
     * @psalm-var class-string
     */
    public string $source;

    /** @var Property[] */
    public array $properties;

    /** @var string[] */
    public array $relationships;

    /** @var string[] */
    public array $attributes;

    /**
     * @param Property[] $properties
     */
    public function __construct(string $source, array $properties)
    {
        $this->source = $source;
        $this->relationships = [];
        $this->attributes = [];

        $this->properties = $properties;
        $this->cache();
    }

    public function source(): string
    {
        return $this->source;
    }

    /**
     * @throws UnknownPropertyException
     */
    public function property(string $name): Property
    {
        if (array_key_exists($name, $this->properties) === false) {
            throw new UnknownPropertyException(
                sprintf('Property "%s" is unknown for "%s"', $name, $this->source)
            );
        }

        return $this->properties[$name];
    }

    public function relationships(): array
    {
        return $this->relationships;
    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    private function cache(): void
    {
        foreach ($this->properties as $accessor => $property) {
            switch (true) {
                case $property->hasSchema() && $property->schemaType() === EntitySchema::class:
                    $this->relationships[] = $accessor;
                    break;
                default:
                    $this->attributes[] = $accessor;
            }
        }
    }
}
