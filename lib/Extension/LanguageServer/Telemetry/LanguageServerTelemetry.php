<?php

namespace Phpactor\Extension\LanguageServer\Telemetry;

use Generator;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\ScopeInterface;
use Phpactor\Extension\OpenTelemetry\Model\ClassHook;
use Phpactor\Extension\OpenTelemetry\Model\HookProvider;
use Phpactor\Extension\OpenTelemetry\Model\PostContext;
use Phpactor\Extension\OpenTelemetry\Model\PreContext;
use Phpactor\Extension\OpenTelemetry\Model\TracerContext;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use function Amp\call;

final class LanguageServerTelemetry implements HookProvider
{
    public function hooks(): Generator
    {
        yield new ClassHook(
            MiddlewareDispatcher::class,
            'dispatch',
            function (TracerContext $tracer, PreContext $context) {
                return $tracer->spanBuilder(
                    $context,
                    self::resolveSpanName($context->param(0, Message::class)),
                )->startSpan();
            },
            function (TracerContext $tracer, PostContext $context) {
                return call(function () use ($tracer, $context) {
                    $return = yield $context->returnValue;
                    $scope = $tracer->storage()->scope();
                    assert($scope instanceof ScopeInterface);
                    $scope->detach();
                    $span = Span::fromContext($scope->context());
                    assert($span instanceof SpanInterface);
                    $span->end();

                    return $return;
                });
            },
        );
    }

    private static function resolveSpanName(Message $message): string
    {
        if ($message instanceof RequestMessage) {
            return sprintf('request %s', $message->method);
        }

        if ($message instanceof NotificationMessage) {
            return sprintf('notification %s', $message->method);
        }

        if ($message instanceof ResponseMessage) {
            return 'response';
        }

        return $message::class;
    }
}
