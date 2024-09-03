<?php

declare(strict_types=1);

namespace Maximaster\Jaft\TypeConverter;

use Maximaster\Jaft\Contract\TypeConverter;
use Maximaster\Jaft\Exception\UnknownExternalTypeException;
use Symfony\Component\PropertyInfo\Type;

/**
 * PHP to Open API type converter based on Symfony PropertyInfo component.
 */
class PropertyInfoTypeConverter implements TypeConverter
{
    /**
     * @var array|string[]
     *
     * @psalm-var array<string,string>
     */
    private array $map;

    public function __construct(?array $map = null)
    {
        $this->map = $map ?? [
            Type::BUILTIN_TYPE_INT => 'integer',
            Type::BUILTIN_TYPE_FLOAT => 'number',
            Type::BUILTIN_TYPE_STRING => 'string',
            Type::BUILTIN_TYPE_BOOL => 'boolean',
            Type::BUILTIN_TYPE_TRUE => 'boolean',
            Type::BUILTIN_TYPE_FALSE => 'boolean',
            Type::BUILTIN_TYPE_RESOURCE => 'object',
            Type::BUILTIN_TYPE_OBJECT => 'object',
            Type::BUILTIN_TYPE_ARRAY => 'array',
            Type::BUILTIN_TYPE_NULL => 'string',
        ];
    }

    public function convertType(string $externalType): string
    {
        if (array_key_exists($externalType, $this->map) === false) {
            throw new UnknownExternalTypeException(
                sprintf(
                    "'%s' can't be converter into Open API. Allowed: %s",
                    $externalType,
                    implode(', ', array_keys($this->map))
                )
            );
        }

        return $this->map[$externalType];
    }
}
