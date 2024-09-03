<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Schema;

use Maximaster\Jaft\Contract\ObjectSchema;
use Maximaster\Jaft\Contract\SchemaRef;

/**
 * A reference to schema which will be loaded on demand.
 */
class LazySchemaRef implements SchemaRef
{
    /**
     * @psalm-var class-string<ObjectSchema> $type
     */
    private string $type;

    /**
     * @var callable
     *
     * @psalm-var Callable():ObjectSchema $fetched
     */
    private $fetcher;

    /**
     * @var ObjectSchema|null schema or null if not loaded
     */
    private ?ObjectSchema $schema = null;

    /**
     * @psalm-var class-string
     */
    private string $source;

    /**
     * @psalm-param class-string $source
     * @psalm-param class-string<ObjectSchema> $type
     * @psalm-param Callable():ObjectSchema $fetched
     */
    public function __construct(string $source, string $type, callable $fetcher)
    {
        $this->source = $source;
        $this->type = $type;
        $this->fetcher = $fetcher;
    }

    public function unwrap(): ObjectSchema
    {
        if ($this->schema === null) {
            $this->schema = ($this->fetcher)();
        }

        return $this->schema;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function source(): string
    {
        return $this->source;
    }
}
