<?php

declare(strict_types=1);

namespace Maximaster\Jaft\CaseConverter;

use Jawira\CaseConverter\CaseConverterException;
use Jawira\CaseConverter\Convert;
use Maximaster\Jaft\Contract\CaseConverter;
use Maximaster\Jaft\Exception\UnsupportedConversionException;

/**
 * Convert names using jawira/case-converter.
 */
class JawiraCaseConverter implements CaseConverter
{
    private string $fromCase;
    private string $toCase;

    /**
     * @throws UnsupportedConversionException
     */
    public function __construct(string $fromCase, string $toCase)
    {
        $this->fromCase = $this->getMethod('from', $fromCase);
        $this->toCase = $this->getMethod('to', $toCase);
    }

    public function convertCase(string $input): string
    {
        return (new Convert($input))->{$this->fromCase}()->{$this->toCase}();
    }

    /**
     * @throws UnsupportedConversionException
     */
    private function getMethod(string $direction, string $case): string
    {
        $method = $direction . ucfirst($case);

        // it's regular method
        if ($method === 'fromAuto') {
            return $method;
        }

        try {
            (new Convert(''))->__call($method, []);
        } catch (CaseConverterException $e) {
            throw new UnsupportedConversionException(
                sprintf(
                    'Conversion %s %s is unsupported',
                    $direction,
                    $case
                ),
                1,
                $e
            );
        }

        return $method;
    }
}
