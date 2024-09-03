<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Contract;

use IteratorAggregate;
use Symfony\Component\PropertyInfo\Type;

/**
 * Embeddable object which packs its properties inside.
 */
interface PackedEmbeddable extends IteratorAggregate
{
    /**
     * @return Type[]
     *
     * @psalm-return array<string,Type>
     */
    public static function properties(): array;
}
