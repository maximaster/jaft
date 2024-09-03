<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Mapper.
 */
interface Mapper
{
    /**
     * @psalm-return array{data: array<non-empty-string, mixed>, included: list<array<non-empty-string, mixed>>}
     */
    public function one(object $entity, ?MapperQuery $query = null): array;

    /**
     * @template AnyObject of object
     * @psalm-param iterable<AnyObject> $entities
     * @psalm-return array{data: list<array<non-empty-string, mixed>>, included: list<array<non-empty-string, mixed>>}
     */
    public function many(iterable $entities, ?MapperQuery $query = null): array;
}
