<?php

namespace Phpactor\Extension\LanguageServerCallHierarchy;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerCallHierarchy\Handler\CallHierarchyHandler;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\ReferenceFinder\ChainReferenceFinder;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\WorseReferenceFinder\MethodCallReferenceFinder;
use Phpactor\WorseReferenceFinder\TolerantVariableReferenceFinder;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Reflector;

class LanguageServerCallHierarchyExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(CallHierarchyHandler::class, function (Container $container) {
            $workspace = $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class);
            $astProvider = $container->get(AstProvider::class);
            $variableFinder = new TolerantVariableReferenceFinder($astProvider);
            $methodCallFinder = new MethodCallReferenceFinder($astProvider, $workspace);
            $referenceFinder = new ChainReferenceFinder([$variableFinder, $methodCallFinder]);

            return new CallHierarchyHandler(
                $workspace,
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $referenceFinder,
                $astProvider,
                $container->expect(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR, DefinitionLocator::class),
            );
        }, [
            LanguageServerExtension::TAG_METHOD_HANDLER => [],
        ]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
