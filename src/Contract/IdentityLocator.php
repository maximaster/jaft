<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Locate identity for an entity class.
 */
interface IdentityLocator
{
    /**
     * Get identity property of given entity class.
     *
     * @psalm-param class-string $entityClass
     */
    public function getIdentity(string $entityClass): string;

    /**
     * Figure out if given class has identity i.e. it's an entity.
     *
     * @psalm-param class-string $entityClass
     */
    public function hasIdentity(string $entityClass): bool;
}
