<?php

declare(strict_types=1);

use Maximaster\Jaft\Contract\Property;
use Maximaster\Jaft\IdentityLocator\StaticNameIdentityLocator;
use Maximaster\Jaft\NamingProvider\FqcnNamingProvider;
use Maximaster\Jaft\PropertyListExtractor\AccessorPropertyListExtractor;
use Maximaster\Jaft\SchemaRepository\PropertyInfoSchemaRepository;
use Maximaster\Jaft\Spec\Stub\Entity\BookInterface;
use Maximaster\Jaft\Spec\Stub\Entity\UserStamp;
use Maximaster\Jaft\TypeConverter\PropertyInfoTypeConverter;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

$makeRepository = function (): PropertyInfoSchemaRepository {
    $reflectionExtractor = new ReflectionExtractor([], ['']);
    $phpDocExtractor = new PhpDocExtractor(null, null, ['']);

    return new PropertyInfoSchemaRepository(
        new PropertyInfoExtractor(
            [new AccessorPropertyListExtractor()],
            [$phpDocExtractor, $reflectionExtractor],
            [$phpDocExtractor, $reflectionExtractor],
            [$phpDocExtractor, $reflectionExtractor],
            [$phpDocExtractor, $reflectionExtractor]
        ),
        new FqcnNamingProvider('%s'),
        new StaticNameIdentityLocator('id'),
        new PropertyInfoTypeConverter(),
        [UserStamp::class]
    );
};

describe(PropertyInfoSchemaRepository::class, function () use ($makeRepository) {
    it('should fetch schema from interface', function () use ($makeRepository) {
        $schema = $makeRepository()->get(BookInterface::class);

        expect($schema->source())->toBe(BookInterface::class);
        expect($schema->type())->toBe('BookInterface');
        expect($schema->idProperty())->toBeAnInstanceOf(Property::class);
        expect($schema->attributes())->toBe(['id', 'title', 'created.at']);
        expect($schema->relationships())->toBe(['authors', 'publisher', 'created.by']);
    });
});
