<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

final class PreContext
{
    public function __construct(
        public object $object,
        public array $params,
        public string $class,
        public string $function,
        public ?string $filename,
        public ?int $lineno
    )
    {
    }

}
