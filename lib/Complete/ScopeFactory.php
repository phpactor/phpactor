<?php

namespace Phpactor\Complete;

use Phpactor\Complete\ScopeResolver;
use PhpParser\ParserFactory;
use PhpParser\Lexer;
use Phpactor\CodeContext;

class ScopeFactory
{
    public function create(CodeContext $codeContext): Scope
    {
        $source = $this->fixSource($codeContext->getSource());

        $lexer = new Lexer([ 'usedAttributes' => [ 'comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos' ] ]);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer, []);
        $stmts = $parser->parse($source);

        foreach ($stmts as $stmt) {
            $scope = (new ScopeResolver())->__invoke($stmt, $codeContext->getOffset());

            if ($scope) {
                return $scope;
            }
        }


        throw new \InvalidArgumentException(sprintf(
            'Could not resolve scope for source with offset "%s"', $codeContext->getOffset()
        ));
    }

    /**
     * If the parser encounters a dangling object operator ("->") it
     * will discard the variable it is associated with.
     *
     * This hack will try and normalize it by adding "xxx" to the object operator.
     */
    private function fixSource(string $source)
    {
        $buffer = [];
        $inFetch = false;
        $tokens = token_get_all($source);

        foreach ($tokens as $index => $token) {

            if ($token[0] === T_OBJECT_OPERATOR) {
                $next = $tokens[$index + 1];

                if (!$next) {
                    break;
                }

                if ($next[0] !== T_STRING) {
                    $buffer[] = '->xxx';
                    continue;
                }

            }

            if ($token[0] === T_DOUBLE_COLON) {
                $next = $tokens[$index + 1];

                if (!$next) {
                    break;
                }

                if ($next[0] !== T_STRING) {
                    $buffer[] = '::xxx';
                    continue;
                }
            }

            $buffer[] = isset($token[1]) ? $token[1] : $token;
        }

        $source = implode('', $buffer);

        return $source;
    }
}
