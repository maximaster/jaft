<?php

declare(strict_types=1);

namespace Maximaster\Jaft\SchemaRepository;

use Maximaster\Jaft\Contract\EntitySchema as EntitySchemaInterface;
use Maximaster\Jaft\Contract\IdentityLocator;
use Maximaster\Jaft\Contract\NamingProvider;
use Maximaster\Jaft\Contract\ObjectSchema as ObjectSchemaInterface;
use Maximaster\Jaft\Contract\Property as PropertyInterface;
use Maximaster\Jaft\Contract\SchemaRef;
use Maximaster\Jaft\Contract\SchemaRepository;
use Maximaster\Jaft\Contract\TypeConverter;
use Maximaster\Jaft\Exception\MalformedEntityException;
use Maximaster\Jaft\Exception\NonObjectPropertyException;
use Maximaster\Jaft\Jaft;
use Maximaster\Jaft\Schema\EntitySchema;
use Maximaster\Jaft\Schema\LazySchemaRef;
use Maximaster\Jaft\Schema\ObjectSchema;
use Maximaster\Jaft\Schema\Property;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * Schema repository based on Symfony PropertyInfo component.
 */
class PropertyInfoSchemaRepository implements SchemaRepository
{
    private NamingProvider $namingProvider;
    private IdentityLocator $identityLocator;
    private PropertyInfoExtractorInterface $propertyExtractor;
    private TypeConverter $typeConverter;

    /**
     * @var SchemaRef[]
     *
     * @psalm-var array<class-string,SchemaRef>
     */
    private array $refs;

    /**
     * @var string[] Classes which can be embeddables
     *
     * @psalm-var list<class-string>
     */
    private array $embeddables;

    /**
     * @var array Value object classes which schemas don't need to be loaded
     *
     * @psalm-var list<class-string>
     */
    private array $valueObjects;

    /**
     * @psalm-param list<class-string> $embeddables
     * @psalm-param list<class-string> $valueObjects
     */
    public function __construct(
        PropertyInfoExtractorInterface $propertyExtractor,
        NamingProvider $namingProvider,
        IdentityLocator $identityLocator,
        TypeConverter $typeConverter,
        array $embeddables = [],
        array $valueObjects = []
    ) {
        $this->propertyExtractor = $propertyExtractor;
        $this->namingProvider = $namingProvider;
        $this->identityLocator = $identityLocator;
        $this->typeConverter = $typeConverter;
        $this->embeddables = $embeddables;
        $this->valueObjects = $valueObjects;

        $this->refs = [];
    }

    /**
     * @psalm-param class-string $className
     */
    public function get(string $className, array $context = []): ObjectSchemaInterface
    {
        return $this->getRef($className, $context)->unwrap();
    }

    /**
     * {@inheritdoc}.
     *
     * @psalm-param class-string $className
     */
    public function has(string $className): bool
    {
        return array_key_exists($className, $this->refs);
    }

    private function getRef(string $className, array $context): SchemaRef
    {
        if (array_key_exists($className, $this->refs) === false) {
            $this->refs[$className] = new LazySchemaRef(
                $className,
                $this->identityLocator->hasIdentity($className)
                    ? EntitySchemaInterface::class
                    : ObjectSchemaInterface::class,
                fn () => $this->createSchema($className, $context)
            );
        }

        return $this->refs[$className];
    }

    /**
     * @throws MalformedEntityException
     * @throws NonObjectPropertyException
     *
     * @psalm-param array<string,mixed> $context
     */
    private function createSchema(string $className, array $context): ObjectSchemaInterface
    {
        $properties = $this->describeProperties($className, '', $context);

        if ($this->identityLocator->hasIdentity($className)) {
            $idName = $this->identityLocator->getIdentity($className);

            return new EntitySchema(
                $className,
                $this->namingProvider->getClassName($className),
                $idName,
                $properties
            );
        }

        return new ObjectSchema($className, $properties);
    }

    /**
     * @return PropertyInterface[]
     *
     * @throws MalformedEntityException
     * @throws NonObjectPropertyException
     *
     * @psalm-param array<string,mixed> $context
     */
    private function describeProperties(string $entityClass, string $prefix, array $context): array
    {
        $properties = [];
        foreach ($this->propertyExtractor->getProperties($entityClass, $context) ?? [] as $propertyName) {
            $types = $this->propertyExtractor->getTypes($entityClass, $propertyName, $context);

            if ($types === null) {
                throw new MalformedEntityException(
                    sprintf("Property '%s' doesn't have type definitions.", $propertyName)
                );
            }

            $property = $this->describeProperty($entityClass, $propertyName, $types, $context);
            $this->saveProperty($properties, $prefix . $propertyName, $property, $context);
        }

        return $properties;
    }

    /**
     * @throws MalformedEntityException
     * @throws NonObjectPropertyException
     *
     * @psalm-param array<string,mixed> $context
     */
    private function saveProperty(
        array &$properties,
        string $propertyName,
        PropertyInterface $property,
        array $context
    ): void {
        // property can be embeddable, so we should be able to split it
        if ($property->hasSchema() && $this->isEmbeddable($property->schemaSource())) {
            $properties += $this->describeProperties(
                $property->schemaSource(),
                $propertyName . '.',
                [Jaft::CONTEXT_PACKED_PROPERTY => $propertyName] + $context
            );

            return;
        }

        $properties[$propertyName] = $property;
    }

    private function isEmbeddable(string $className): bool
    {
        foreach ($this->embeddables as $embeddable) {
            if (is_a($className, $embeddable, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws MalformedEntityException
     *
     * @psalm-param array<string,mixed> $context
     */
    private function describeProperty(
        string $entityClass,
        string $name,
        array $types,
        array $context
    ): PropertyInterface {
        try {
            $type = $this->pickType($types);
            $itemType = $this->pickItemType($type);
        } catch (MalformedEntityException $e) {
            throw new MalformedEntityException(
                sprintf('%s on %s.%s', $e->getMessage(), $entityClass, $name),
                $e->getCode(),
                $e
            );
        }

        $schema = null;
        $itemClass = $itemType->getClassName();
        if ($itemClass !== null && $this->isValueObject($itemClass) === false) {
            $schema = $this->getRef($itemClass, $context);
        }

        return new Property(
            $name,
            $this->typeConverter->convertType($itemType->getBuiltinType()),
            $type->isCollection(),
            $schema
        );
    }

    private function isValueObject(string $className): bool
    {
        foreach ($this->valueObjects as $valueObject) {
            if (is_a($className, $valueObject, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Type[] $types
     *
     * @throws MalformedEntityException
     */
    private function pickType(array $types): Type
    {
        foreach ($types as $type) {
            return $type;
        }

        throw new MalformedEntityException('No types provided');
    }

    private function pickItemType(Type $type): Type
    {
        $itemType = $type->getCollectionValueType();

        if ($type->isCollection() === false || $itemType === null) {
            return $type;
        }

        return $itemType;
    }
}
