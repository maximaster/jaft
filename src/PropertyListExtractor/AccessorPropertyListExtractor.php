<?php

declare(strict_types=1);

namespace Maximaster\Jaft\PropertyListExtractor;

use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;

/**
 * List entity properties by accessor methods.
 */
class AccessorPropertyListExtractor implements PropertyListExtractorInterface
{
    /**
     * @var string[][]
     *
     * @psalm-var array<string, list<class-string>>
     */
    private array $ignoredMethods;

    /**
     * @var bool[]
     *
     * @psalm-var array<string, bool>
     */
    private array $ignoredCache = [];

    /**
     * @param string[] $ignoredMethods Methods to ignore
     *
     * @throws ReflectionException
     */
    public function __construct(array $ignoredMethods = [])
    {
        $this->ignoredMethods = $this->compileIgnoreMethods($ignoredMethods);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ReflectionException
     */
    public function getProperties(string $class, array $context = []): array
    {
        $properties = [];

        $reflection = new ReflectionClass($class);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (
                $method->isStatic()
                || $method->getNumberOfParameters() > 0
                || $this->isIgnored($method)
            ) {
                continue;
            }

            $returnType = $method->getReturnType();
            if (($returnType instanceof ReflectionNamedType) === false) {
                continue;
            }

            if ($returnType->getName() === 'void') {
                continue;
            }

            $properties[] = $method->name;
        }

        return $properties;
    }

    /**
     * @param string[] $ignoredMethods
     *
     * @return string[][]
     *
     * @throws ReflectionException
     *
     * @psalm-return array<string, list<class-string>>
     */
    private function compileIgnoreMethods(array $ignoredMethods): array
    {
        $compiledMethods = [];
        foreach ($ignoredMethods as $ignoredMethod) {
            $ignoredMethod = new ReflectionMethod($ignoredMethod);

            if (array_key_exists($ignoredMethod->name, $compiledMethods) === false) {
                $compiledMethods[$ignoredMethod->name] = [];
            }

            $compiledMethods[$ignoredMethod->name][] = $ignoredMethod->class;
        }

        return $compiledMethods;
    }

    private function isIgnored(ReflectionMethod $method): bool
    {
        $fqmn = $method->class . '::' . $method->name;
        if (array_key_exists($fqmn, $this->ignoredCache)) {
            return $this->ignoredCache[$fqmn];
        }

        // Ignored by static configuration?
        if (array_key_exists($method->name, $this->ignoredMethods)) {
            foreach ($this->ignoredMethods[$method->name] as $ignoredClass) {
                if (is_a($method->class, $ignoredClass, true)) {
                    return true;
                }
            }
        }

        // Ignored by docblock comment
        $docComment = $method->getDocComment();
        if (is_string($docComment) && strpos($docComment, '@jaft-ignore') !== false) {
            return $this->ignoredCache[$fqmn] = true;
        }

        try {
            return $this->isIgnored($method->getPrototype());
        } catch (Exception $e) {
            return $this->ignoredCache[$fqmn] = false;
        }
    }
}
