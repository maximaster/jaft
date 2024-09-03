<?php

declare(strict_types=1);

namespace Maximaster\Jaft\LinksGenerator;

use Maximaster\Jaft\Contract\LinksGenerator;
use Maximaster\Jaft\Exception\UnsupportedLinksGeneratorException;

/**
 * Links generator which delegates its work to injected links generators.
 */
class ChainLinksGenerator implements LinksGenerator
{
    /** @var LinksGenerator[] */
    private iterable $linksGenerators;

    /**
     * @var array classname to generator
     *
     * @psalm-var array<string, LinksGenerator>
     */
    private array $cache = [];

    /**
     * @param LinksGenerator[] $linksGenerators
     */
    public function __construct(iterable $linksGenerators)
    {
        $this->linksGenerators = $linksGenerators;
    }

    public function supports(object $entity): bool
    {
        return $this->findGenerator($entity) !== null;
    }

    public function generateLinks(object $entity): array
    {
        $generator = $this->findGenerator($entity);
        if ($generator === null) {
            throw new UnsupportedLinksGeneratorException(
                sprintf('There is no links generator to generate links for %s', get_class($entity))
            );
        }

        return $generator->generateLinks($entity);
    }

    private function findGenerator(object $entity): ?LinksGenerator
    {
        $cacheKey = spl_object_hash($entity);
        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        foreach ($this->linksGenerators as $linksGenerator) {
            if ($linksGenerator->supports($entity)) {
                return $this->cache[$cacheKey] = $linksGenerator;
            }
        }

        return null;
    }
}
