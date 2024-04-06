<?php

use JetBrains\PhpStorm\Deprecated;

class ClassOne
{
    public const FOO = 'bar';

    #[Deprecated]
    public const MOO = 'bar', ZOO = 'bar';
}
