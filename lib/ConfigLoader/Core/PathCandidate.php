<?php

namespace Phpactor\ConfigLoader\Core;

interface PathCandidate
{
    public function path(): string;

    public function loader(): string;
}
