<?php

declare(strict_types=1);

namespace Maximaster\Jaft;

use Exception as PhpException;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;
use League\Fractal\TransformerAbstract;
use Maximaster\Jaft\Contract\CaseConverter;
use Maximaster\Jaft\Contract\DataExtractor;
use Maximaster\Jaft\Contract\LinksGenerator;
use Maximaster\Jaft\Contract\NamingProvider;
use Maximaster\Jaft\Contract\ObjectFormatter;
use Maximaster\Jaft\Contract\Property;
use Maximaster\Jaft\Contract\SchemaRepository;
use Maximaster\Jaft\Exception\Exception;
use Maximaster\Jaft\Exception\UnsupportedLinksGeneratorException;

/**
 * JSON:API fractal transformer.
 */
class Jaft extends TransformerAbstract
{
    public const CONTEXT_PACKED_PROPERTY = 'jaft_packed_property';
    public const CONTEXT_ENTITY = 'jaft_entity';

    private SchemaRepository $schemas;
    private DataExtractor $extractor;
    private ?ObjectFormatter $formatter;
    private ?CaseConverter $nameConverter;
    private ?LinksGenerator $linksGenerator;
    private NamingProvider $namingProvider;

    /** @psalm-var array<non-empty-string, array<mixed>> */
    private array $transformCache = [];

    /** @psalm-var array<non-empty-string, array<mixed>> */
    private array $processIncludedResourcesCache = [];

    public function __construct(
        SchemaRepository $schemas,
        DataExtractor $extractor,
        NamingProvider $namingProvider,
        ?ObjectFormatter $formatter = null,
        ?CaseConverter $nameConverter = null,
        ?LinksGenerator $linksGenerator = null
    ) {
        $this->schemas = $schemas;
        $this->extractor = $extractor;
        $this->namingProvider = $namingProvider;
        $this->formatter = $formatter;
        $this->nameConverter = $nameConverter;
        $this->linksGenerator = $linksGenerator;
    }

    public function getAvailableIncludes(): array
    {
        $data = $this->getCurrentScope()->getResource()->getData();
        $resource = is_array($data) ? reset($data) : $data;

        // empty collection doesn't have available includes
        if (is_object($resource) === false) {
            return [];
        }

        $allIncludes = $this->schemas->get(get_class($resource))->relationships();
        $fieldset = $this->currentScope
            ->getManager()
            ->getFieldset($this->currentScope->getResource()->getResourceKey());

        return $fieldset === null
            ? $allIncludes
            : array_intersect($allIncludes, iterator_to_array($fieldset->getIterator()));
    }

    /**
     * @throws UnsupportedLinksGeneratorException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) TODO
     * @SuppressWarnings(PHPMD.NPathComplexity) TODO
     */
    public function transform(object $entity): array
    {
        /** @psalm-var non-empty-string $cacheKey */
        $cacheKey = spl_object_hash($entity);
        if (array_key_exists($cacheKey, $this->transformCache)) {
            return $this->transformCache[$cacheKey];
        }

        $fields = [];

        // TODO revisit
        foreach ($this->extractor->attributes($entity) as $key => $value) {
            $callback = fn (&$value) => $value = (
                $this->formatter !== null
                && is_object($value)
                && $this->formatter->supports($value)
            ) ? $this->formatter->format($value) : $value;

            if (is_array($value)) {
                array_walk_recursive($value, $callback);

                $fields[$key] = $value;
            }

            if (is_array($value) === false) {
                $fields[$key] = $callback($value);
            }
        }

        if ($this->nameConverter !== null) {
            $fields = array_combine(
                array_map([$this->nameConverter, 'convertCase'], array_keys($fields)),
                $fields
            );
        }

        if ($this->linksGenerator !== null && $this->linksGenerator->supports($entity)) {
            $fields['links'] = $this->linksGenerator->generateLinks($entity);
        }

        return $this->transformCache[$cacheKey] = ([
            /**
             * TODO only here we can get real object type, because when we serialize collection we would have collection
             *      resource type only and then array, not object. It's better to enhance fractal to enable it get item
             *      type of the collection, but it's to hard to implement right now.
             *      JSON:API forbrids to have "type" attribute, so it's rather safe to put it here, to unset later.
             *
             * @see \Maximaster\Jaft\Serializer\JsonApiSerializer::item
             */
            'type' => $this->namingProvider->getObjectName($entity),
        ] + $fields);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function processIncludedResources(Scope $scope, $data)
    {
        /** @psalm-var non-empty-string|null $cacheKey */
        $cacheKey = is_object($data)
            ? spl_object_hash($scope) . $scope->getIdentifier() . spl_object_hash($data)
            : null;

        if ($cacheKey !== null && array_key_exists($cacheKey, $this->processIncludedResourcesCache)) {
            return $this->processIncludedResourcesCache[$cacheKey];
        }

        $included = parent::processIncludedResources($scope, $data);

        if ($this->nameConverter !== null && is_array($included)) {
            $included = array_combine(
                array_map([$this->nameConverter, 'convertCase'], array_keys($included)),
                $included
            );

            if ($included === false) {
                throw new Exception('Unexpected array_combine error.');
            }
        }

        if ($cacheKey !== null) {
            $this->processIncludedResourcesCache[$cacheKey] = $included;
        }

        return $included;
    }

    /**
     * @param $method
     * @param $arguments
     *
     * @return Item
     *
     * @throws Exception
     */
    public function __call($method, $arguments)
    {
        $match = [];
        if (preg_match('/^include(.+)/', $method, $match) === 1) {
            [, $property] = $match;

            return $this->includeResource(...array_merge([lcfirst($property)], $arguments));
        }

        throw new Exception(sprintf('Method "%s" is not implemented', $method));
    }

    /**
     * {@inheritDoc}.
     *
     * @param string $includeName
     *
     * @return false|ResourceInterface
     */
    protected function callIncludeMethod(Scope $scope, $includeName, $data)
    {
        $resource = $this->includeResource($includeName, $data);
        if ($resource === null) {
            return false;
        }

        if (($resource instanceof ResourceInterface) === false) {
            throw new PhpException(sprintf(
                'Invalid return value for %s::%s. Expected %s, received %s.',
                get_class($data),
                $includeName,
                ResourceInterface::class,
                is_object($resource) ? get_class($resource) : gettype($resource)
            ));
        }

        return $resource;
    }

    /**
     * @throws Exception
     */
    private function includeResource(string $property, object $entity)
    {
        $schema = $this->schemas->get(get_class($entity));

        $propertySchema = $schema->property($property);
        $value = $this->extractor->get($entity, $property);

        // nullable relationship
        if ($value === null) {
            return null;
        }

        $propertyType = $propertySchema->iterable()
            ? $propertySchema->schema()->type()
            // ignore schema (it can show abstract type), get real type
            : $this->namingProvider->getObjectName($value);

        return $propertySchema->iterable()
            ? $this->collection($this->formatCollection($propertySchema, $value), $this, $propertyType)
            : $this->item($value, $this, $propertyType);
    }

    /**
     * @throws Exception
     */
    private function formatCollection(Property $property, $value): iterable
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_iterable($value)) {
            return iterator_to_array($value);
        }

        throw new Exception(
            sprintf(
                'Iterable was expected in %s, %s given: %s',
                $property->name(),
                get_debug_type($value),
                var_export($value, true)
            )
        );
    }
}
