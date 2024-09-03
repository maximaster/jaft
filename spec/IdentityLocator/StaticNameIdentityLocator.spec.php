<?php

declare(strict_types=1);

use Maximaster\Jaft\IdentityLocator\StaticNameIdentityLocator;

describe(StaticNameIdentityLocator::class, function (): void {
    it('should find property', function (): void {
        $entity = new class () {
            public string $id;
        };

        $locator = new StaticNameIdentityLocator('id');
        expect($locator->hasIdentity(get_class($entity)))->toBeTruthy();
    });

    it('should find getter', function (): void {
        $entity = new class () {
            public function getId(): string
            {
                return '';
            }
        };

        $locator = new StaticNameIdentityLocator('id');
        expect($locator->hasIdentity(get_class($entity)))->toBeTruthy();
    });

    it('should find accessor', function (): void {
        $entity = new class () {
            public function id(): string
            {
                return '';
            }
        };

        $locator = new StaticNameIdentityLocator('id');

        expect($locator->hasIdentity(get_class($entity)))->toBeTruthy();
    });

    it('should fail to find unknown property', function (): void {
        $entity = new class () {
        };
        $locator = new StaticNameIdentityLocator('id');

        expect($locator->hasIdentity(get_class($entity)))->toBeFalsy();
    });
});
