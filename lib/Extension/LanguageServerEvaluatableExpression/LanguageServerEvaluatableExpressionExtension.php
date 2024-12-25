<?php

namespace Phpactor\Extension\LanguageServerEvaluatableExpression;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\ObjectRenderer\ObjectRendererExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServerEvaluatableExpression\Handler\EvaluatableExpressionHandler;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class LanguageServerEvaluatableExpressionExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('language_server_completion.handler.evaluatable_expression', function (Container $container) {
            return new EvaluatableExpressionHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(WorseReflectionExtension::SERVICE_PARSER),
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => []]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
