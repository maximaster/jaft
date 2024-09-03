<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Serializer;

use League\Fractal\Serializer\JsonApiSerializer as FractalJsonApiSerializer;
use Maximaster\Jaft\Contract\CaseConverter;
use stdClass;

/**
 * JsonApiSerializer which supports embaddables and various types in collections.
 */
class JsonApiSerializer extends FractalJsonApiSerializer
{
    private ?CaseConverter $nameConverter;
    private bool $tidyLinks;

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag) TODO revisit
     */
    public function __construct($baseUrl = null, ?CaseConverter $nameConverter = null, bool $tidyLinks = true)
    {
        parent::__construct($baseUrl);

        $this->nameConverter = $nameConverter;
        $this->tidyLinks = $tidyLinks;
    }

    public function injectAvailableIncludeData($data, $availableIncludes): array
    {
        if ($this->nameConverter !== null) {
            $availableIncludes = array_map([$this->nameConverter, 'convertCase'], $availableIncludes);
        }

        switch ($this->isCollection($data)) {
            case true:
                $data['data'] = array_map(function ($resource) use ($availableIncludes) {
                    foreach ($availableIncludes as $relationshipKey) {
                        $resource = $this->addRelationshipLinks($resource, $relationshipKey);
                    }

                    return $resource;
                }, $data['data']);
                break;
            case false:
            default:
                foreach ($availableIncludes as $relationshipKey) {
                    $data['data'] = $this->addRelationshipLinks($data['data'], $relationshipKey);
                }
        }

        return $data;
    }

    /**
     * Adds links for all available includes to a single resource.
     *
     * @param array $resource         The resource to add relationship links to
     * @param string $relationshipKey The resource key of the relationship
     */
    private function addRelationshipLinks($resource, $relationshipKey): array
    {
        if (
            isset($resource['relationships']) === false
            || isset($resource['relationships'][$relationshipKey]) === false
        ) {
            $resource['relationships'][$relationshipKey] = [];
        }

        return $resource;
    }

    public function item($resourceKey, array $data): array
    {
        /**
         * @see \Maximaster\Jaft\Jaft::transform return section for explanation
         */
        if (array_key_exists('type', $data)) {
            $resourceKey = $data['type'];
            unset($data['type']);
        }

        $resource = parent::item($resourceKey, $data);

        // Why are they doing that? Everything is arrays and then out of the
        // blue - stdClass?
        if ($resource['data']['attributes'] instanceof stdClass) {
            $resource['data']['attributes'] = [];
        }

        if ($this->tidyLinks) {
            unset($resource['data']['links']['self'], $resource['data']['links']['related']);
        }

        return $resource;
    }
}
