<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Spec\Stub\Entity;

use DateTimeImmutable;

class UserStamp
{
    private UserInterface $by;
    private DateTimeImmutable $at;

    public function __construct(UserInterface $by, DateTimeImmutable $at)
    {
        $this->by = $by;
        $this->at = $at;
    }

    public function by(): UserInterface
    {
        return $this->by;
    }

    public function at(): DateTimeImmutable
    {
        return $this->at;
    }
}
