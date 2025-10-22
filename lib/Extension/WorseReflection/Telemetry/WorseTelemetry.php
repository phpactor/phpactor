<?php

namespace Phpactor\Extension\WorseReflection\Telemetry;

use Generator;
use Microsoft\PhpParser\Parser;
use OpenTelemetry\API\Trace\SpanKind;
use Phpactor\Extension\OpenTelemetry\Model\ClassHook;
use Phpactor\Extension\OpenTelemetry\Model\HookProvider;
use Phpactor\Extension\OpenTelemetry\Model\PreContext;
use Phpactor\Extension\OpenTelemetry\Model\TracerContext;
use Phpactor\WorseReflection\Core\Reflector\CoreReflector;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Reflector;
use ReflectionClass;

class WorseTelemetry implements HookProvider
{
    public function hooks(): Generator
    {
        $reflection = new ReflectionClass(Reflector::class);
        foreach ($reflection->getMethods() as $method) {
            yield new ClassHook(CoreReflector::class, $method->getName(), function (TracerContext $tracing, PreContext $context) use ($method) {
                return $tracing->spanBuilder(
                    $context,
                    $method->getName()
                )->setSpanKind(SpanKind::KIND_INTERNAL)->setParent($context->context())->startSpan();
            });
        }
        $reflection = new ReflectionClass(SourceCodeReflector::class);
        foreach ($reflection->getMethods() as $method) {
            yield new ClassHook(SourceCodeReflector::class, $method->getName(), function (TracerContext $tracing, PreContext $context) use ($method) {
                return $tracing->spanBuilder(
                    $context,
                    $method->getName()
                )->setSpanKind(SpanKind::KIND_INTERNAL)->setParent($context->context())->startSpan();
            });
        }

        yield new ClassHook(SourceCodeLocator::class, 'locate', function (TracerContext $tracing, PreContext $context) {
            return $tracing->spanBuilder(
                $context,
                $context->object::class,
            )->setSpanKind(SpanKind::KIND_INTERNAL)->setParent(
                $context->context()
            )->startSpan();
        });

        yield new ClassHook(Parser::class, 'parseSourceFile', function (TracerContext $tracing, PreContext $context) {
            return $tracing
                ->spanBuilder($context, 'tolerant-php-parser')
                ->setSpanKind(SpanKind::KIND_INTERNAL)
                ->setParent($context->context())
                ->setAttribute('parser-file', $context->param(1))
                ->startSpan();
        });
    }
}
