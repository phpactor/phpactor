<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

use Generator;

interface HookProvider
{
    /**
    * @return Generator<ClassHook>
     */
    public function hooks(): Generator;
}
