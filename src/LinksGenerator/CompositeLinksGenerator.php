<?php

declare(strict_types=1);

namespace Maximaster\Jaft\LinksGenerator;

use Maximaster\Jaft\Contract\LinksGenerator;
use Maximaster\Jaft\Exception\UnsupportedLinksGeneratorException;

/**
 * Collects links from another link generators which supports tested entity class.
 */
class CompositeLinksGenerator implements LinksGenerator
{
    /** @var LinksGenerator[] */
    private iterable $linksGenerators;

    /**
     * Кеш ссылок.
     *
     * @psalm-var array<non-empty-string, array<non-empty-string, string>>
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
        return count($this->findGenerators($entity)) > 0;
    }

    /**
     * {@inheritDoc}
     *
     * @throws UnsupportedLinksGeneratorException
     */
    public function generateLinks(object $entity): array
    {
        /** @psalm-var non-empty-string $cacheKey */
        $cacheKey = spl_object_hash($entity);

        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        $links = [];
        foreach ($this->findGenerators($entity) as $generator) {
            $links = array_merge($links, $generator->generateLinks($entity));
        }

        return $this->cache[$cacheKey] = $links;
    }

    /**
     * @return LinksGenerator[]
     */
    private function findGenerators(object $entity): array
    {
        $generators = [];
        foreach ($this->linksGenerators as $linksGenerator) {
            if ($linksGenerator->supports($entity)) {
                $generators[] = $linksGenerator;
            }
        }

        return $generators;
    }
}
