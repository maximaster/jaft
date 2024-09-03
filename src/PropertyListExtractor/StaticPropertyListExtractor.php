<?php

declare(strict_types=1);

namespace Maximaster\Jaft\PropertyListExtractor;

use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;

/**
 * Statically bound property lists to class names.
 */
class StaticPropertyListExtractor implements PropertyListExtractorInterface
{
    /**
     * @var array|string[][]
     *
     * @psalm-var array<string,string[]>
     */
    private array $classProperties;

    /**
     * @psalm-param array<string,string[]> $classProperties
     */
    public function __construct(array $classProperties)
    {
        $this->classProperties = $classProperties;
    }

    /**
     * {@inheritDoc}
     */
    public function getProperties(string $class, array $context = []): ?array
    {
        if (array_key_exists($class, $this->classProperties) === false) {
            return null;
        }

        return $this->classProperties[$class];
    }
}
