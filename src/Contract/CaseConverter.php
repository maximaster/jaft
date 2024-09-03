<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Convert string into desired case.
 */
interface CaseConverter
{
    /**
     * @psalm-param non-empty-string $input
     * @psalm-return non-empty-string
     */
    public function convertCase(string $input): string;
}
