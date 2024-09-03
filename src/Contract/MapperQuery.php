<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

/**
 * Query for mapping.
 */
interface MapperQuery
{
    public const QUERY_INCLUDE = 'include';
    public const QUERY_FIELDS = 'fields';

    /**
     * @return array which resources to include
     *
     * @psalm-return list<non-empty-string>
     */
    public function includes(): array;

    /**
     * @return array which fields for each type to include
     *
     * @psalm-return array<non-empty-string, non-empty-string>
     */
    public function fields(): array;
}
