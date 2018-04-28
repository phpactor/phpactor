<?php

namespace Phpactor\Extension\Navigation\Navigator;

interface Navigator
{
    public function destinationsFor(string $path): array;
}
