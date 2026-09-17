<?php

namespace Phpactor\Extension\LanguageServerCallHierarchy;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerCallHierarchy\Handler\CallHierarchyHandler;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\ReferenceFinder\ChainReferenceFinder;
use Phpactor\WorseReferenceFinder\MethodCallReferenceFinder;
use Phpactor\WorseReferenceFinder\TolerantVariableReferenceFinder;
use Phpactor\WorseReflection\Core\AstProvider;

class LanguageServerCallHierarchyExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(CallHierarchyHandler::class, function (Container $container) {
            $workspace = $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE);
            $astProvider = $container->get(AstProvider::class);
            $variableFinder = new TolerantVariableReferenceFinder($astProvider);
            $methodCallFinder = new MethodCallReferenceFinder($astProvider, $workspace);
            $referenceFinder = new ChainReferenceFinder([$variableFinder, $methodCallFinder]);

            return new CallHierarchyHandler(
                $workspace,
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $referenceFinder,
                $astProvider,
                $container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR),
            );
        }, [
            LanguageServerExtension::TAG_METHOD_HANDLER => [],
        ]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
