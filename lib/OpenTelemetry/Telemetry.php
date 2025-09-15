<?php

namespace Phpactor\OpenTelemetry;

use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\Attributes\CodeAttributes;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use function OpenTelemetry\Instrumentation\hook;

class Telemetry
{
    public static function register(CachedInstrumentation $instrumentation): void
    {
        hook(
            MiddlewareDispatcher::class,
            'dispatch',
            pre: static function (
                MiddlewareDispatcher $handler,
                array $params,
                string $class,
                string $function,
                ?string $filename,
                ?int $lineno,
            ) use ($instrumentation): void {
                $message = $params[0];
                assert($message instanceof Message);
                if ($message instanceof RequestMessage) {
                    $name = sprintf('REQ %s', $message->method);
                } elseif ($message instanceof NotificationMessage) {
                    $name = sprintf('NOT %s', $message->method);
                } elseif ($message instanceof ResponseMessage) {
                    $name = 'RESPONSE';
                } else {
                    return;
                }

                $parent = Context::getCurrent();
                $builder = $instrumentation
                    ->tracer()
                    ->spanBuilder($name)
                    ->setSpanKind(SpanKind::KIND_SERVER)
                    ->setAttribute('params', $params)
                    ->setAttribute(CodeAttributes::CODE_FUNCTION_NAME, sprintf('%s::%s', $class, $function))
                    ->setParent($parent)
                    ->setAttribute(CodeAttributes::CODE_FILE_PATH, $filename)
                    ->setAttribute(CodeAttributes::CODE_LINE_NUMBER, $lineno);

                $span = $builder->startSpan();
                Context::storage()->attach($span->storeInContext($parent));
            },
            post: static function (
                MiddlewareDispatcher $handler,
                array $params,
            ): void {
                $scope = Context::storage()->scope();
                if (null === $scope) {
                    return;
                }
                $scope->detach();
                $span = Span::fromContext($scope->context());
                $span->end();
            }
        );
    }
}
