<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Spec\Stub\Entity;

class Book implements BookInterface
{
    private string $id;
    private string $title;
    private array $authors;
    private PublisherInterface $publisher;
    private UserStamp $created;

    public function __construct(
        string $id,
        string $title,
        array $authors,
        PublisherInterface $publisher,
        UserStamp $created
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->authors = $authors;
        $this->publisher = $publisher;
        $this->created = $created;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function authors(): array
    {
        return $this->authors;
    }

    public function publisher(): PublisherInterface
    {
        return $this->publisher;
    }

    public function created(): UserStamp
    {
        return $this->created;
    }
}
