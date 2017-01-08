<?php

namespace Phpactor\Complete;

use Phpactor\Reflection\ReflectorInterface;
use Phpactor\Complete\Provider\VariableProvider;
use PhpParser\Lexer;
use PhpParser\ParserFactory;
use Phpactor\Complete\ScopeResolver;
use Phpactor\Complete\ScopeFactory;
use Phpactor\Complete\Suggestions;
use Phpactor\CodeContext;

class Completer
{
    /**
     * @var ProviderInterface
     */
    private $providers = [];

    /**
     * @var ScopeFactory
     */
    private $scopeFactory;

    public function __construct(ScopeFactory $scopeFactory, array $providers)
    {
        $this->providers = $providers;
        $this->scopeFactory = $scopeFactory;
    }

    public function complete(CodeContext $codeContext): Suggestions
    {
        $scope = $this->scopeFactory->create($codeContext);
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
