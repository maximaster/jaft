<?php

declare(strict_types=1);

namespace Maximaster\Jaft\ObjectFormatter;

use Maximaster\Atoa\Contract\Atoa;
use Maximaster\Atoa\Contract\Exception\UnsupportedConversionException;
use Maximaster\Jaft\Contract\ObjectFormatter;

/**
 * Object formatter based on Atoa.
 */
class AtoaObjectFormatter implements ObjectFormatter
{
    private Atoa $atoa;

    /** @var string[] */
    private array $supportedTypes;

    /** @psalm-var array<non-empty-string, string|null> */
    private array $cache = [];

    /**
     * @param string[] $supportedTypes
     */
    public function __construct(Atoa $atoa, ?array $supportedTypes = null)
    {
        $this->atoa = $atoa;
        $this->supportedTypes = is_array($supportedTypes)
            ? $supportedTypes
            : array_merge(PHP_TYPE_SCALAR, [PHP_TYPE_ARRAY]);
    }

    public function supports(object $object): bool
    {
        return $this->findAvailableOutput($object) !== null;
    }

    /**
     * @return int|float|string|array
     *
     * @throws UnsupportedConversionException
     */
    public function format(object $object)
    {
        $output = $this->findAvailableOutput($object);
        if ($output === null) {
            throw new UnsupportedConversionException(sprintf('Conversion from %s is impossible', get_class($object)));
        }

        return $this->atoa->convertTo($output, $object);
    }

    private function findAvailableOutput(object $object): ?string
    {
        $key = get_class($object);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        foreach ($this->supportedTypes as $supportedType) {
            if ($this->atoa->available(get_class($object), $supportedType)) {
                return $this->cache[$key] = $supportedType;
            }
        }

        return $this->cache[$key] = null;
    }
}
