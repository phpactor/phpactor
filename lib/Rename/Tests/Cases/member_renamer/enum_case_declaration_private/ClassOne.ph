<?php

use JetBrains\PhpStorm\Deprecated;

enum ClassOne
{
    case BAR;
    #[Deprecated]
    case BAZ;
}
