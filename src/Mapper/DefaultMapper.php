<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Mapper;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\TransformerAbstract;
use Maximaster\Jaft\Contract\Mapper;
use Maximaster\Jaft\Contract\MapperQuery;
use Maximaster\Jaft\Contract\NamingProvider;

/**
 * Default Jaft mapper.
 */
class DefaultMapper implements Mapper
{
    private Manager $manager;
    private TransformerAbstract $transformer;
    private NamingProvider $namingProvider;

    public function __construct(Manager $manager, TransformerAbstract $transformer, NamingProvider $namingProvider)
    {
        $this->manager = $manager;
        $this->transformer = $transformer;
        $this->namingProvider = $namingProvider;
    }

    public function one(object $entity, ?MapperQuery $query = null): array
    {
        $item = new Item($entity, $this->transformer, $this->namingProvider->getObjectName($entity));

        return $this->mapResource($item, $query);
    }

    public function many(iterable $entities, ?MapperQuery $query = null): array
    {
        /**
         * @noinspection PhpParamsInspection
         * @phpstan-ignore-next-line why:correct
         */
        $entities = iterator_to_array($entities);

        $collection = new Collection($entities, $this->transformer);

        if (count($entities) > 0) {
            $collection->setResourceKey($this->namingProvider->getObjectName(reset($entities)));
        }

        return $this->mapResource($collection, $query);
    }

    /**
     * @template ResourceType of ResourceAbstract
     * @psalm-param ResourceType $resource
     * @psalm-return ($resource is Item
     *  ? array{data: array<non-empty-string, mixed>, included: list<array<non-empty-string, mixed>>}
     *  : array{data: list<array<non-empty-string, mixed>>, included: list<array<non-empty-string, mixed>>})
     */
    private function mapResource(ResourceAbstract $resource, ?MapperQuery $query = null): array
    {
        if ($query !== null) {
            $this->manager->parseIncludes($query->includes());
            $this->manager->parseFieldsets($query->fields());
        }

        // @phpstan-ignore-next-line why:correct
        return $this->manager->createData($resource)->toArray();
    }
}
