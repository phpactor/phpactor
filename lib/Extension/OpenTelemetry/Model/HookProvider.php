<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

use Generator;

/**
 * Opinionated abstraction for the OpenTelemetry SDK.
 */
interface HookProvider
{
    /**
    * @return Generator<ClassHook>
     */
    public function hooks(): Generator;
}
