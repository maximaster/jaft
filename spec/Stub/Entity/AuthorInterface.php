<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Spec\Stub\Entity;

interface AuthorInterface
{
    public function id(): string;

    public function name(): string;
}
