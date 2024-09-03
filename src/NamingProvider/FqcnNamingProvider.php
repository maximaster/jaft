<?php

declare(strict_types=1);

namespace Maximaster\Jaft\NamingProvider;

use Maximaster\Jaft\Contract\CaseConverter;
use Maximaster\Jaft\Contract\NamingProvider;

/**
 * Provides name from FQCN by formatting it.
 */
class FqcnNamingProvider implements NamingProvider
{
    private string $format;
    private ?CaseConverter $converter;
    /** @psalm-var array<class-string, string> */
    private array $cache = [];

    public function __construct(string $format, ?CaseConverter $converter = null)
    {
        $this->format = $format;
        $this->converter = $converter;
    }

    public function getClassName(string $entityClass): string
    {
        if (array_key_exists($entityClass, $this->cache) === false) {
            $parts = explode('\\', ltrim($entityClass, '\\'));
            if ($this->converter !== null) {
                $parts = array_map([$this->converter, 'convertCase'], $parts);
            }

            $this->cache[$entityClass] = sprintf(str_replace('%s', end($parts), $this->format), ...$parts);
        }

        return $this->cache[$entityClass];
    }

    public function getObjectName(object $entity): string
    {
        return $this->getClassName(get_class($entity));
    }
}
