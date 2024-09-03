<?php

declare(strict_types=1);

namespace Maximaster\Jaft\PropertyTypeExtractor;

use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;

/**
 * Reads types from another type.
 */
class ReplacedPropertyTypeExtractor implements PropertyTypeExtractorInterface
{
    /**
     * @var string[]
     *
     * @psalm-var array<class-string, class-string>
     */
    private array $map;

    private PropertyTypeExtractorInterface $realExtractor;

    /**
     * @psalm-param array<class-string, class-string> $map
     */
    public function __construct(PropertyTypeExtractorInterface $realExtractor, array $map = [])
    {
        $this->realExtractor = $realExtractor;
        $this->map = $map;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-param array<mixed> $context
     */
    public function getTypes(string $class, string $property, array $context = []): ?array
    {
        foreach ($this->map as $inputClass => $outputClass) {
            if (is_a($class, $inputClass, true)) {
                return $this->realExtractor->getTypes($outputClass, $property, $context);
            }
        }

        return null;
    }
}
