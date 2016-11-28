<?php

namespace Phpactor\Tests\Unit\Complete;

use Phpactor\Complete\CompleteContext;
use Phpactor\Complete\ScopeResolver;
use PhpParser\ParserFactory;
use Phpactor\Complete\Scope;

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
        $scope = $context->getScope();
        $this->assertEquals($expectedScope, (string) $scope);
        $this->assertEquals('Foobar\Barfoo', $scope->getNamespace());
    }

    public function provideScope()
    {
        return [
            [ 5, Scope::SCOPE_GLOBAL ],
            [ 7, Scope::SCOPE_FUNCTION ],
            [ 9, Scope::SCOPE_FUNCTION ],
            [ 8, Scope::SCOPE_FUNCTION ],
            [ 13, Scope::SCOPE_CLASS ],
            [ 15, Scope::SCOPE_CLASS ],
            [ 16, Scope::SCOPE_CLASS ],
            [ 17, Scope::SCOPE_CLASS_METHOD ],
            [ 18, Scope::SCOPE_CLASS_METHOD ],
            [ 21, Scope::SCOPE_CLASS ],
            [ 22, Scope::SCOPE_CLASS ],
            [ 30, Scope::SCOPE_GLOBAL ],
        ];
    }

    private function getContext(string $source, $lineNb)
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $stmts = $parser->parse($source);

        return new CompleteContext($stmts, $lineNb);
    }
}
