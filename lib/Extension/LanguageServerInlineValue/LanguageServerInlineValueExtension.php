<?php

namespace Phpactor\Extension\LanguageServerInlineValue;

use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServerInlineValue\Handler\InlineValueHandler;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class LanguageServerInlineValueExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('language_server_inline_value.handler', function (Container $container) {
            return new InlineValueHandler(
                $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class),
                $container->expect(WorseReflectionExtension::SERVICE_PARSER, AstProvider::class),
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => []]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
