<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use RuntimeException;

final class PreContext
{
    public function __construct(
        public object $object,
        public array $params,
        public string $class,
        public string $function,
        public ?string $filename,
        public ?int $lineno
    ) {
    }

    public function context(): ContextInterface
    {
        return Context::getCurrent();
    }

    /**
     * @template TType of object|null
     * @param class-string<TType> $class|null
     * @return ($class is class-string<TType> ? TType : mixed)
     */
    public function param(int $offset, ?string $class = null): mixed
    {
        if (!isset($this->params[$offset])) {
            throw new RuntimeException(sprintf(
                'No parameter at offset %d, there are %d parameters',
                $offset,
                count($this->params),
            ));
        }

        $value = $this->params[$offset];
        if ($class === null) {
            return $value;
        }

        if (!$value instanceof $class) {
            throw new RuntimeException(sprintf(
                'Expected param %d to be of class "%s" but it\'s "%s"',
                $offset,
                $class,
                get_debug_type($class)
            ));
        }

        return $value;
    }

    public function attachSpanToParent(SpanInterface $span): void
    {
        Context::storage()->attach($span->storeInContext($parent));
    }

}
