<?php

namespace Phpactor\Complete;

use Phpactor\Reflection\ReflectorInterface;
use Phpactor\Complete\Provider\VariableProvider;
use PhpParser\Lexer;
use PhpParser\ParserFactory;

class Completer
{
    public function complete(string $source, int $lineNb, int $columnNb)
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $stmts = $parser->parse($source);

        $lines = explode(PHP_EOL, $source);
        $line = $lines[$lineNb - 1];
        $line = trim(substr($line, 0, $columnNb));

        $lexer = new Lexer();
        $lexer->startLexing('<?php ' . $line);
        $tokens = $lexer->getTokens();

        // unshift "<?php"
        array_shift($tokens);

        $completeContext = new CompleteContext(
            $stmts,
            $tokens,
            $lineNb
        );
        $scope = $completeContext->getScope();
        var_dump($scope);die();;

        $varProvider = new VariableProvider();
        $varProvider->provide($completeContext);
    }
}
