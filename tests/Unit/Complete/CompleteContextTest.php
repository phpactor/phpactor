<?php

namespace Phpactor\Tests\Unit\Complete;

use Phpactor\Complete\CompleteContext;
use Phpactor\Complete\ScopeResolver;
use PhpParser\ParserFactory;
use Phpactor\Complete\Scope;
use PhpParser\Lexer;

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
            [ 1, Scope::SCOPE_GLOBAL ],
            [ 33, Scope::SCOPE_GLOBAL ],
            [ 65, Scope::SCOPE_GLOBAL ],
            [ 66, Scope::SCOPE_FUNCTION ],
            [ 125, Scope::SCOPE_FUNCTION ],
            [ 126, Scope::SCOPE_FUNCTION ],
            [ 130, Scope::SCOPE_CLASS ],
            [ 172, Scope::SCOPE_CLASS ],
            [ 174, Scope::SCOPE_CLASS_METHOD ],
            [ 247, Scope::SCOPE_CLASS_METHOD ],
            [ 249, Scope::SCOPE_CLASS ],
            [ 250, Scope::SCOPE_CLASS ],
            [ 251, Scope::SCOPE_CLASS ],
            [ 281, Scope::SCOPE_CLASS_METHOD ],
            [ 335, Scope::SCOPE_CLASS ],
            [ 338, Scope::SCOPE_GLOBAL ],
        ];
    }

    private function getContext(string $source, $lineNb)
    {
        $lexer = new Lexer([ 'usedAttributes' => [ 'startFilePos', 'endFilePos' ]]);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer);
        $stmts = $parser->parse($source);

        return new CompleteContext($stmts, $lineNb);
    }
}
