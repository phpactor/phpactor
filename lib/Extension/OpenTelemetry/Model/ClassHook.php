<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

use OpenTelemetry\API\Trace\SpanInterface;

final class ClassHook
{
    /**
     * @param class-string $class
     * @param callable(TracerContext,PreContext):SpanInterface $pre
     * @param (callable(TracerContext,PostContext):mixed) $post
     */
    public function __construct(
        public string $class,
        public string $function,
        public $pre,
        public $post = null,
    ) {
    }
}
