<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Schema provider.
 */
interface SchemaRepository
{
    /**
     * @psalm-param class-string $className
     * @psalm-param array<string, mixed> $context
     */
    public function get(string $className, array $context = []): ObjectSchema;

    /**
     * Does it has schema for specified entity class?
     *
     * @psalm-param class-string
     */
    public function has(string $className): bool;
}
