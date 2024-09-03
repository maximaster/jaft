<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Entity data extractor (id, attributes).
 */
interface DataExtractor
{
    /**
     * Get entity identifier.
     */
    public function id(object $entity): string;

    /**
     * @psalm-return array<string,mixed>
     */
    public function attributes(object $entity): array;

    /**
     * Get entity relationship values.
     *
     * @psalm-return array<string,object>
     */
    public function relationships(object $entity): array;

    /**
     * Get available relationships.
     *
     * @return string[]
     */
    public function availableRelationships(object $entity): array;

    /**
     * Get value from specified entity property.
     *
     * @param string $propertyPath nested path is supported
     */
    public function get(object $entity, string $propertyPath);
}
