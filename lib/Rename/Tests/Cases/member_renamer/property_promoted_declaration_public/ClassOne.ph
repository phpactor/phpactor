<?php

namespace Test;

use JetBrains\PhpStorm\Deprecated;

class ClassOne
{
    public function __construct(
        public string $foobar,
        #[Deprecated]
        private string $depOld,
    ) {
    }

    public function bar(): string
    {
        return $this->foobar;
    }
}
