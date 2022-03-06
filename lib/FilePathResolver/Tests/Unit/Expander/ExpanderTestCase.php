<?php

namespace Phpactor\FilePathResolver\Tests\Unit\Expander;

use PHPUnit\Framework\TestCase;
use Phpactor\FilePathResolver\Expander;
use Phpactor\FilePathResolver\Expanders;
use Phpactor\FilePathResolver\Filter\TokenExpandingFilter;

abstract class ExpanderTestCase extends TestCase
{
    abstract public function createExpander(): Expander;

    protected function expand(string $path): string
    {
        return (new TokenExpandingFilter(new Expanders([ $this->createExpander() ])))->apply($path);
    }
}
