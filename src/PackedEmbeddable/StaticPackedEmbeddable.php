<?php

declare(strict_types=1);

namespace Maximaster\Jaft\PackedEmbeddable;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Maximaster\Jaft\Contract\PackedEmbeddable;
use Maximaster\Jaft\Exception\Exception;
use Symfony\Component\PropertyInfo\Type;
use Traversable;

/**
 * PackedEmbeddable which contains array.
 */
abstract class StaticPackedEmbeddable implements PackedEmbeddable, ArrayAccess
{
    private ?array $values = null;

    /**
     * @var callable
     *
     * @psalm-var Callable():array
     */
    private $valuesPromise;

    /**
     * @param Type[] $properties
     * @param string[] $extraInterfaces
     *
     * @return string|PackedEmbeddable
     *
     * @psalm-param array<string, Type> $properties
     * @psalm-param list<class-string> $extraInterfaces
     * @psalm-return class-string
     *
     * @SuppressWarnings(PHPMD.EvalExpression) why:intended
     */
    public static function generate(array $properties, ?string $className = null, array $extraInterfaces = []): string
    {
        static $generated = 0;
        ++$generated;

        $className = $className ?? static::class . '\\Runtime' . $generated;

        $classNameParts = explode('\\', $className);
        $classShortname = array_pop($classNameParts);
        $classNamespace = implode('\\', $classNameParts);

        $rawProperties = [];
        foreach ($properties as $name => $property) {
            $rawProperties[$name] = static::unpackType($property);
        }

        eval(sprintf(
            <<<PHP
                namespace %s;

                class %s extends \\%s %s
                {
                    public static function rawProperties(): array
                    {
                        return %s;
                    }
                }
            PHP,
            $classNamespace,
            $classShortname,
            self::class,
            count($extraInterfaces) > 0
                ? 'implements ' . implode(', ', preg_replace('/^/', '\\', $extraInterfaces))
                : '',
            var_export($rawProperties, true)
        ));

        return $className;
    }

    public static function fromArray(array $values): self
    {
        return new static(fn () => $values);
    }

    /**
     * @psalm-param Callable():array $valuesPromise
     */
    public static function fromArrayPromise(callable $valuesPromise): self
    {
        return new static($valuesPromise);
    }

    /**
     * @psalm-param Callable():array $valuesPromise
     */
    private function __construct(callable $valuesPromise)
    {
        $this->valuesPromise = $valuesPromise;
    }

    private function values(): array
    {
        if ($this->values === null) {
            $this->values = ($this->valuesPromise)();
        }

        return $this->values;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values());
    }

    /**
     * {@inheritDoc}
     */
    public static function properties(): array
    {
        $properties = [];
        foreach (static::rawProperties() as $name => $property) {
            $properties[$name] = self::buildType($property);
        }

        return $properties;
    }

    abstract protected static function rawProperties(): array;

    protected static function unpackType(Type $type): array
    {
        $collectionKeyType = $type->getCollectionKeyType();
        $collectionValueType = $type->getCollectionValueType();

        return [
            $type->getBuiltinType(),
            $type->isNullable(),
            $type->getClassName(),
            $type->isCollection(),
            $collectionKeyType === null ? null : self::unpackType($collectionKeyType),
            $collectionValueType === null ? null : self::unpackType($collectionValueType),
        ];
    }

    protected static function buildType(array $type): Type
    {
        [$builtinType, $nullable, $className, $collection, $collectionKeyType, $collectionValueType] = $type;

        $collectionKeyTypes = $collectionKeyType === null ? null : self::buildType($collectionKeyType);
        $collectionValueTypes = $collectionValueType === null ? null : self::buildType($collectionValueType);

        return new Type($builtinType, $nullable, $className, $collection, $collectionKeyTypes, $collectionValueTypes);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->values());
    }

    public function offsetGet($offset)
    {
        return $this->values()[$offset] ?? null;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function offsetSet($offset, $value): void
    {
        throw new Exception('Collection is static thus should not be edited');
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('Collection is static thus should not be edited');
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __call($name, $arguments)
    {
        if ($this->offsetExists($name)) {
            return $this->offsetGet($name);
        }

        throw new BadMethodCallException(sprintf('%s::%s is not implemented', self::class, $name));
    }
}
