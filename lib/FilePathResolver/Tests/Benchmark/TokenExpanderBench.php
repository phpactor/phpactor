<?php

namespace Phpactor\FilePathResolver\Tests\Benchmark;

use Phpactor\FilePathResolver\Expander\ValueExpander;
use Phpactor\FilePathResolver\Expanders;
use Phpactor\FilePathResolver\Filter\TokenExpandingFilter;

/**
 * @BeforeMethods({"setUp"})
 * @Revs(10000)
 * @Iterations(33)
 */
class TokenExpanderBench
{
    private TokenExpandingFilter $tokenExpander;

    public function setUp(): void
    {
        $expanders = new Expanders([
            new ValueExpander('a', 'A'),
            new ValueExpander('b', 'A'),
            new ValueExpander('c', 'A'),
            new ValueExpander('d', 'A'),
            new ValueExpander('e', 'A'),
        ]);

        $this->tokenExpander = new TokenExpandingFilter($expanders);
    }

    public function benchExpandTokenizedString(): void
    {
        $this->tokenExpander->apply('%a%/%b%/%c%/%d%/%e%');
    }

    public function benchExpandStringWithNoTokens(): void
    {
        $this->tokenExpander->apply('a/b/c/d/e');
    }
}
