<?php

namespace Phpactor\Extension\LanguageServerEvaluatableExpression;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServerEvaluatableExpression\Handler\EvaluatableExpressionHandler;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Microsoft\PhpParser\Parser;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class LanguageServerEvaluatableExpressionExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('language_server_completion.handler.evaluatable_expression', function (Container $container) {
            return new EvaluatableExpressionHandler(
                $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class),
                $container->expect(WorseReflectionExtension::SERVICE_PARSER, Parser::class),
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => []]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
