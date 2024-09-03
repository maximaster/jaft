<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Spec\Stub\Entity;

interface BookInterface
{
    public function id(): string;

    public function title(): string;

    /**
     * @return AuthorInterface[]
     */
    public function authors(): array;

    public function publisher(): PublisherInterface;

    public function created(): UserStamp;
}
