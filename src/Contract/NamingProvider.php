<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Provides name for an entity class.
 */
interface NamingProvider
{
    /**
     * Derive API name from entity class name.
     *
     * @psalm-param class-string $entityClass
     */
    public function getClassName(string $entityClass): string;

    /**
     * Derive API name from entity object.
     */
    public function getObjectName(object $entity): string;
}
