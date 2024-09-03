<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Spec\Stub\Entity;

class Author implements AuthorInterface
{
    public function id(): string
    {
        return '999129ba-d1be-11eb-b6e3-db2c9aeb3068';
    }

    public function name(): string
    {
        return 'Someone';
    }
}
