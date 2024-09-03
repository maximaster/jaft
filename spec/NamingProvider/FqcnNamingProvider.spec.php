<?php

declare(strict_types=1);

use Maximaster\Jaft\NamingProvider\FqcnNamingProvider;

describe(FqcnNamingProvider::class, function (): void {
    it('should convert name using both %{number} and %s', function (): void {
        $naming = new FqcnNamingProvider('%3$s-%s');

        $name = $naming->getClassName('\\Vendor\\Package\\SuperPackage\\Inner\\Path\\Name');
        expect($name)->toBe('SuperPackage-Name');
    });

    it('should convert name using only %{number}', function (): void {
        $naming = new FqcnNamingProvider('%1$s-%3$s');

        $name = $naming->getClassName('\\Vendor\\Package\\SuperPackage\\Inner\\Path\\Name');
        expect($name)->toBe('Vendor-SuperPackage');
    });

    it('should convert name using only %s', function (): void {
        $naming = new FqcnNamingProvider('%s');

        $name = $naming->getClassName('\\Vendor\\Package\\SuperPackage\\Inner\\Path\\Name');
        expect($name)->toBe('Name');
    });
});
