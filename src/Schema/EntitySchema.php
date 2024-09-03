<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Schema;

use Maximaster\Jaft\Contract\EntitySchema as EntitySchemaInterface;
use Maximaster\Jaft\Contract\Property;

/**
 * Entity schema.
 */
class EntitySchema extends ObjectSchema implements EntitySchemaInterface
{
    /**
     * @var string entity JSON:API type name
     */
    public string $type;

    /**
     * @var string name of the property which stores id
     */
    public string $idProperty;

    /**
     * @param Property[] $properties
     */
    public function __construct(string $source, string $type, string $idProperty, array $properties)
    {
        parent::__construct($source, $properties);

        $this->type = $type;
        $this->idProperty = $idProperty;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function idProperty(): Property
    {
        return $this->properties[$this->idProperty];
    }
}
