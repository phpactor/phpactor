<?php

namespace Phpactor\Tests\Unit\Complete;

use Phpactor\Complete\CompleteContext;
use Phpactor\Complete\ScopeResolver;
use PhpParser\ParserFactory;

class CompleteContextTest extends \PHPUnit_Framework_TestCase
{
    private $context;

    /**
     * @dataProvider provideScope
     */
    public function testScope(int $lineNb, string $expectedScope)
    {
        $source = file_get_contents(__DIR__ . '/source/context_scope.php');
        $context = $this->getContext($source, $lineNb);
        $this->assertEquals($expectedScope, $context->getScope());
    }

    public function provideScope()
    {
        return [
            [ 3, ScopeResolver::SCOPE_GLOBAL ],
            [ 5, ScopeResolver::SCOPE_FUNCTION ],
            [ 7, ScopeResolver::SCOPE_FUNCTION ],
            [ 6, ScopeResolver::SCOPE_FUNCTION ],
            [ 11, ScopeResolver::SCOPE_CLASS ],
            [ 13, ScopeResolver::SCOPE_CLASS ],
            [ 14, ScopeResolver::SCOPE_CLASS ],
            [ 15, ScopeResolver::SCOPE_CLASS_METHOD ],
            [ 16, ScopeResolver::SCOPE_CLASS_METHOD ],
            [ 19, ScopeResolver::SCOPE_CLASS ],
            [ 20, ScopeResolver::SCOPE_CLASS ],
            [ 28, ScopeResolver::SCOPE_GLOBAL ],
        ];
    }

    private function getContext(string $source, $lineNb)
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $stmts = $parser->parse($source);

        return new CompleteContext($stmts, $lineNb);
    }
}
