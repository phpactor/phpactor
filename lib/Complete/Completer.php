<?php

namespace Phpactor\Complete;

use Phpactor\Reflection\ReflectorInterface;
use Phpactor\Complete\Provider\VariableProvider;
use PhpParser\Lexer;
use PhpParser\ParserFactory;

class Completer
{
    /**
     * @var ProviderInterface
     */
    private $providers = [];

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function complete(string $source, int $offset)
    {
        $lexer = new Lexer([ 'usedAttributes' => [ 'startFilePos', 'endFilePos' ] ]);

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer, []);
        $stmts = $parser->parse($source);

        $completeContext = new CompleteContext(
            $stmts,
            $offset
        );

        $suggestions = [];
        foreach ($this->providers as $provider) {
            if (false === $provider->canProvideFor($completeContext)) {
                continue;
            }

            $suggestions = array_merge($suggestions, $provider->provide($completeContext));
        }

        return $suggestions;
    }
}
