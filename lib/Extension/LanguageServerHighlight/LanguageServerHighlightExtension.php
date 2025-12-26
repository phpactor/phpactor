<?php

namespace Phpactor\Extension\LanguageServerHighlight;

use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\HighlightHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Model\Highlighter;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

final class LanguageServerHighlightExtension implements Extension
{
    const PARAM_ENABLE = 'language_server_highlight.enabled';

    public function load(ContainerBuilder $container): void
    {
        $container->register(HighlightHandler::class, function (Container $container) {
            if ($container->parameter(self::PARAM_ENABLE)->bool() === false) {
                return null;
            }

            return new HighlightHandler(
                $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class),
                new Highlighter($container->expect(WorseReflectionExtension::SERVICE_AST_PROVIDER, AstProvider::class)),
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_ENABLE => true,
        ]);
        $schema->setDescriptions([
            self::PARAM_ENABLE => 'Enable or disable the highlighter (can be expensive on large documents)',
        ]);
    }
}
