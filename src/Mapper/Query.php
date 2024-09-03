<?php

declare(strict_types=1);

namespace Maximaster\Jaft\Mapper;

use Maximaster\Jaft\Contract\MapperQuery;
use Maximaster\Jaft\Exception\Exception;
use Webmozart\Assert\Assert;

/**
 * Default mapper query implementation.
 *
 * @psalm-immutable
 */
class Query implements MapperQuery
{
    /**
     * @var array which resources to include
     *
     * @psalm-var list<non-empty-string>
     */
    public array $includes;

    /**
     * @var array which fields for each type to include
     *
     * @psalm-var array<non-empty-string, non-empty-string>
     */
    public array $fields;

    /**
     * Creates an instance from URI query string.
     *
     * @throws Exception
     */
    public static function fromString(string $queryString): self
    {
        $query = [];
        parse_str($queryString, $query);

        $includes = (
            array_key_exists(self::QUERY_INCLUDE, $query)
            && is_string($query[self::QUERY_INCLUDE])
            && $query[self::QUERY_INCLUDE] !== ''
        )
            ? explode(',', $query[self::QUERY_INCLUDE])
            : [];

        $fields = is_array($query) && array_key_exists(self::QUERY_FIELDS, $query)
            ? $query[self::QUERY_FIELDS]
            : [];

        /** @psalm-var array<non-empty-string, non-empty-string> $fields */
        Assert::allStringNotEmpty($fields);
        Assert::allStringNotEmpty(array_keys($fields));
        Assert::allStringNotEmpty($includes);

        return new self($includes, $fields);
    }

    /**
     * @throws Exception
     *
     * @psalm-param list<non-empty-string> $includes
     * @psalm-param array<non-empty-string, non-empty-string> $fields
     */
    public function __construct(array $includes, array $fields)
    {
        Assert::allStringNotEmpty($includes);

        /** @psalm-var array<non-empty-string, non-empty-string> $fields */
        Assert::allStringNotEmpty($fields);
        Assert::allStringNotEmpty(array_keys($fields));

        $this->includes = $this->prepareIncludes($includes);
        $this->fields = $fields;
    }

    public function includes(): array
    {
        return $this->includes;
    }

    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * Append specified includes and return new Query object.
     *
     * @param string[] $includes
     *
     * @throws Exception
     *
     * @psalm-param list<non-empty-string> $includes
     */
    public function appendIncludes(array $includes): self
    {
        return new self(array_merge($this->includes, $includes), $this->fields);
    }

    /**
     * @throws Exception
     *
     * @psalm-param list<non-empty-string> $includes
     * @psalm-return list<non-empty-string>
     */
    protected function prepareIncludes(array $includes): array
    {
        $prepared = [];
        foreach ($includes as $include) {
            if (strpos($include, '(') === false) {
                $prepared[] = $include;

                continue;
            }

            // it's "(repeatable)" include which means: I want to get parent,parent.parent,... and so on
            if (preg_match('/([^(]*)\(([A-Za-z.]+)\)(.*)/', $include, $match) === false) {
                throw new Exception('Invalid syntax on repeatable include: closing bracket missed.');
            }

            [, $start, $repeatable, $rest] = $match;

            foreach (range(1, 10) as $repeat) {
                $prepared[] = $start . implode('.', array_fill(0, $repeat, $repeatable)) . $rest;
            }
        }

        return $prepared;
    }
}
