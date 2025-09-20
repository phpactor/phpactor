<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

use OpenTelemetry\API\Trace\TracerInterface;

final class ClassHook
{
    /**
     * @param class-string $class
     * @param callable(TracerInterface,PreContext):void $pre
     * @param callable(TracerInterface,PostContext):void $post
     */
    public function __construct(
        public string $class,
        public string $function,
        public $pre,
        public $post
    )
    {
    }
}
