<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

use Throwable;

class PostContext
{
    /**
     * @param array<int,mixed> $params
     */
    public function __construct(public object $object, public array $params, public mixed $returnValue, public ?Throwable $exception)
    {
    }

}
