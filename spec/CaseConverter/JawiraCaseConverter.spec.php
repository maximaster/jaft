<?php

declare(strict_types=1);

use Jawira\CaseConverter\Convert;
use Maximaster\Jaft\CaseConverter\JawiraCaseConverter;

describe(Convert::class, function (): void {
    it('should convert case with known converters', function (): void {
        $converter = new JawiraCaseConverter('pascal', 'kebab');

        expect($converter->convertCase('OneTwoThree'))->toBe('one-two-three');
    });

    it('should fail with unknown input case', function (): void {
        expect(function () {
            $converter = new JawiraCaseConverter('foo', 'kebab');

            return $converter->convertCase('OneTwoThree');
        })->toThrow('Conversion from foo is unsupported');
    });

    it('should fail with unknown outout case', function (): void {
        expect(function () {
            $converter = new JawiraCaseConverter('pascal', 'bar');

            return $converter->convertCase('OneTwoThree');
        })->toThrow('Conversion to bar is unsupported');
    });
});
