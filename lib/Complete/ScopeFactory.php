<?php

namespace Phpactor\Complete;

use Phpactor\Complete\ScopeResolver;
use PhpParser\ParserFactory;
use PhpParser\Lexer;

class ScopeFactory
{
    public function create($source, $offset): Scope
    {
        $lexer = new Lexer([ 'usedAttributes' => [ 'startFilePos', 'endFilePos' ] ]);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer, []);
        $stmts = $parser->parse($source);

        foreach ($stmts as $stmt) {
            $scope = (new ScopeResolver())->__invoke($stmt, $offset);

            if (null === $scope) {
                continue;
            }

            return $scope;
        }

        throw new \InvalidArgumentException(sprintf(
            'Could not resolve scope for source with offset "%s"', $offset
        ));
    }
}
