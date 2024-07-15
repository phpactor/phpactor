<?php

use JetBrains\PhpStorm\Deprecated;

class ClassOne
{
    public string $foobar;

    #[Deprecated]
    public string $found = '';

    public function __construct(string $foobar)
    {
        $this->foobar = $foobar;
    }
}
