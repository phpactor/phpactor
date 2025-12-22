<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use RuntimeException;

final class PreContext
{
    /**
     * @param array<int,mixed> $params
     */
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

    public function param(int $offset): mixed
    {
        if (!isset($this->params[$offset])) {
            throw new RuntimeException(sprintf(
                'No parameter at offset %d, there are %d parameters',
                $offset,
                count($this->params),
            ));
        }

        return $this->params[$offset];
    }
}
