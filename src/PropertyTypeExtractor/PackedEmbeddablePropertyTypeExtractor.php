<?php

declare(strict_types=1);

namespace Maximaster\Jaft\PropertyTypeExtractor;

use Maximaster\Jaft\Contract\PackedEmbeddable;
use Maximaster\Jaft\Jaft;
use ReflectionMethod;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;

/**
 * PackedEmbeddable property type extractor.
 */
class PackedEmbeddablePropertyTypeExtractor implements PropertyTypeExtractorInterface
{
    public function getTypes(string $class, string $property, array $context = []): ?array
    {
        if (
            array_key_exists(Jaft::CONTEXT_ENTITY, $context)
            && array_key_exists(Jaft::CONTEXT_PACKED_PROPERTY, $context)
            && is_object($context[Jaft::CONTEXT_ENTITY])
            && method_exists($context[Jaft::CONTEXT_ENTITY], $context[Jaft::CONTEXT_PACKED_PROPERTY])
        ) {
            // TODO revisit
            $docBlock = (new ReflectionMethod($context[Jaft::CONTEXT_ENTITY], $context[Jaft::CONTEXT_PACKED_PROPERTY]))
                ->getDocComment();

            if ($docBlock !== false && strpos($docBlock, 'PackedEmbeddable') !== false) {
                $class = get_class($context[Jaft::CONTEXT_ENTITY]->{$context[Jaft::CONTEXT_PACKED_PROPERTY]}());
            }
        }

        if (is_subclass_of($class, PackedEmbeddable::class, true) === false) {
            return null;
        }

        $properties = $class::properties();
        if (array_key_exists($property, $properties) === false) {
            return null;
        }

        return [$properties[$property]];
    }
}
