<?php

declare(strict_types=1);

namespace Maximaster\Jaft\IdentityLocator;

use Maximaster\Jaft\Contract\IdentityLocator;
use ReflectionClass;
use ReflectionException;

/**
 * Static name identity locator.
 */
class StaticNameIdentityLocator implements IdentityLocator
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getIdentity(string $entityClass): string
    {
        return $this->name;
    }

    /**
     * @throws ReflectionException
     */
    public function hasIdentity(string $entityClass): bool
    {
        $reflection = new ReflectionClass($entityClass);

        return $reflection->hasProperty($this->name)
            || $reflection->hasMethod('get' . ucfirst($this->name))
            || $reflection->hasMethod($this->name);
    }
}
