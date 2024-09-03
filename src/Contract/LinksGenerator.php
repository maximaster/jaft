<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

use Maximaster\Jaft\Exception\UnsupportedLinksGeneratorException;

/**
 * Links generator for transformer.
 */
interface LinksGenerator
{
    public function supports(object $entity): bool;

    /**
     * @throws UnsupportedLinksGeneratorException
     *
     * @psalm-return array<non-empty-string, string>
     */
    public function generateLinks(object $entity): array;
}
