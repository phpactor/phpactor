<?php

namespace Phpactor\Complete;

use Phpactor\Reflection\ReflectorInterface;
use Phpactor\Complete\Provider\VariableProvider;
use PhpParser\Lexer;
use PhpParser\ParserFactory;
use Phpactor\Complete\ScopeResolver;
use Phpactor\Complete\ScopeFactory;

class Completer
{
    /**
     * @var ProviderInterface
     */
    private $providers = [];

    public function __construct(ScopeFactory $scopeFactory, array $providers)
    {
        $this->providers = $providers;
    }

    public function complete(string $source, int $offset)
    {
        $suggestions = new Suggestions();
        foreach ($this->providers as $provider) {
            if (false === $provider->canProvideFor($scope)) {
                continue;
            }

            $provider->provide($scope, $suggestions);
        }

        return $suggestions;
    }
}
