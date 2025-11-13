<?php

namespace Phpactor\VersionResolver;

use Amp\Promise;

interface SemVersionResolver
{
    /**
     * @return Promise<?SemVersion>
     */
    public function resolve(): Promise;
}
