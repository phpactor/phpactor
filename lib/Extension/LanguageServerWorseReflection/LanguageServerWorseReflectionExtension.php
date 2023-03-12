<?php

namespace Phpactor\Extension\LanguageServerWorseReflection;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerWorseReflection\DiagnosticProvider\WorseDiagnosticProvider;
use Phpactor\Extension\LanguageServerWorseReflection\Handler\InlayHintHandler;
use Phpactor\Extension\LanguageServerWorseReflection\InlayHint\InlayHintOptions;
use Phpactor\Extension\LanguageServerWorseReflection\InlayHint\InlayHintProvider;
use Phpactor\Extension\LanguageServerWorseReflection\SourceLocator\WorkspaceSourceLocator;
use Phpactor\Extension\LanguageServerWorseReflection\Workspace\WorkspaceIndex;
use Phpactor\Extension\LanguageServerWorseReflection\Workspace\WorkspaceIndexListener;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\MapResolver\Resolver;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\ReflectorBuilder;

class LanguageServerWorseReflectionExtension implements Extension
{
    const PARAM_UPDATE_INTERVAL = 'language_server_worse_reflection.workspace_index.update_interval';
    const PARAM_INLAY_HINTS_ENABLE = 'language_server_worse_reflection.inlay_hints.enable';
    const PARAM_INLAY_HINTS_TYPES = 'language_server_worse_reflection.inlay_hints.types';
    const PARAM_INLAY_HINTS_PARAMS = 'language_server_worse_reflection.inlay_hints.params';



    public function load(ContainerBuilder $container): void
    {
        $this->registerSourceLocator($container);
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_UPDATE_INTERVAL => 100,
            self::PARAM_INLAY_HINTS_ENABLE => true,
            self::PARAM_INLAY_HINTS_TYPES => false,
            self::PARAM_INLAY_HINTS_PARAMS => true,
        ]);
        $schema->setDescriptions([
            self::PARAM_UPDATE_INTERVAL => 'Minimum interval to update the workspace index as documents are updated (in milliseconds)'
        ]);
    }

    private function registerSourceLocator(ContainerBuilder $container): void
    {
        $container->register(WorkspaceSourceLocator::class, function (Container $container) {
            return new WorkspaceSourceLocator(
                $container->get(WorkspaceIndex::class)
            );
        }, [ WorseReflectionExtension::TAG_SOURCE_LOCATOR => [
            'priority' => 255,
        ]]);

        $container->register(WorkspaceIndexListener::class, function (Container $container) {
            return new WorkspaceIndexListener(
                $container->get(WorkspaceIndex::class),
            );
        }, [ LanguageServerExtension::TAG_LISTENER_PROVIDER => [] ]);

        $container->register(WorkspaceIndex::class, function (Container $container) {
            return new WorkspaceIndex(
                ReflectorBuilder::create()->build(),
                $container->getParameter(self::PARAM_UPDATE_INTERVAL)
            );
        });

        $container->register(WorseDiagnosticProvier::class, function (Container $container) {
            return new WorseDiagnosticProvider($container->get(WorseReflectionExtension::SERVICE_REFLECTOR));
        }, [ LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('code-action', true) ]);

        $container->register(InlayHintHandler::class, function (Container $container) {
            if (false === $container->getParameter(self::PARAM_INLAY_HINTS_ENABLE)) {
                return null;
            }
            return new InlayHintHandler(
                new InlayHintProvider(
                    $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, SourceCodeReflector::class),
                    new InlayHintOptions(
                        $container->getParameter(self::PARAM_INLAY_HINTS_TYPES),
                        $container->getParameter(self::PARAM_INLAY_HINTS_PARAMS),
                    )
                ),
                $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class)
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => []]);
    }
}
