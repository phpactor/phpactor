<?php

namespace Phpactor\Extension\Behat\Behat\Pattern;

interface PatternPolicy
{
    /**
     * Transforms pattern string to regex.
     *
     * @param string $pattern
     */
    public function transformPatternToRegex($pattern): string;
}
