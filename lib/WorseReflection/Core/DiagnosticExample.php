<?php

namespace Phpactor\WorseReflection\Core;

use Closure;

class DiagnosticExample
{
    /**
     * @param Closure(Diagnostics<Diagnostic>): void $assertion
     */
    public function __construct(
        public string $title,
        public string $source,
        public bool $valid,
        public Closure $assertion
    ) {
    }

}
