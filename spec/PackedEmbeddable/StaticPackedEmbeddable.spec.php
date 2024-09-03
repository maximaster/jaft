<?php

declare(strict_types=1);

use Maximaster\Jaft\PackedEmbeddable\StaticPackedEmbeddable;
use Symfony\Component\PropertyInfo\Type;

describe(StaticPackedEmbeddable::class, function (): void {
    it('should generate', function (): void {
        $propertyTypes = [
            'name' => new Type(Type::BUILTIN_TYPE_STRING),
            'embeddable' => new Type(Type::BUILTIN_TYPE_OBJECT, false, StaticPackedEmbeddable::class),
        ];

        $extractedPropertyTypes = StaticPackedEmbeddable::generate($propertyTypes)::properties();

        expect(array_keys($extractedPropertyTypes))->toBe(array_keys($propertyTypes));

        foreach ($propertyTypes as $propertyName => $propertyType) {
            expect((array) $propertyType)->toBe((array) $extractedPropertyTypes[$propertyName]);
        }
    });
});
