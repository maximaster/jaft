<?php

declare(strict_types=1);

namespace Maximaster\Jaft\DataExtractor;

use Maximaster\Jaft\Contract\DataExtractor;
use Maximaster\Jaft\Contract\SchemaRepository;
use Maximaster\Jaft\Jaft;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Data extractor based on Symfony PropertyAccess component.
 */
class PropertyAccessorDataExtractor implements DataExtractor
{
    private PropertyAccessorInterface $accessor;
    private SchemaRepository $schemas;

    public function __construct(SchemaRepository $schemas, PropertyAccessorInterface $accessor)
    {
        $this->schemas = $schemas;
        $this->accessor = $accessor;
    }

    public function id(object $entity): string
    {
        return $this->accessor->getValue(
            $entity,
            $this->schemas->get(get_class($entity))->idProperty()->name()
        );
    }

    public function attributes(object $entity): array
    {
        $attributes = [];

        $context = [Jaft::CONTEXT_ENTITY => $entity];
        foreach ($this->schemas->get(get_class($entity), $context)->attributes() as $propertyName) {
            $attributes[$propertyName] = $this->getValue($entity, $propertyName);
        }

        return $attributes;
    }

    public function relationships(object $entity): array
    {
        $relationships = [];
        foreach ($this->schemas->get(get_class($entity))->relationships() as $propertyName) {
            $relationship = $this->getValue($entity, $propertyName);
            if ($relationship !== null) {
                $relationships[$propertyName] = $relationship;
            }
        }

        return $relationships;
    }

    public function availableRelationships(object $entity): array
    {
        return array_keys($this->relationships($entity));
    }

    public function get(object $entity, string $propertyPath)
    {
        return $this->getValue($entity, $propertyPath);
    }

    private function getValue(object $entity, string $property)
    {
        return $this->accessor->getValue($entity, $property);
    }
}
