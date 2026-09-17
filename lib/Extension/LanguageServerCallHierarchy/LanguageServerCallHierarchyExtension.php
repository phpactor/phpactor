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
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Reflector;

class LanguageServerCallHierarchyExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(CallHierarchyHandler::class, function (Container $container) {
            return new CallHierarchyHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(ReferenceFinder::class),
                $container->get(AstProvider::class),
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
