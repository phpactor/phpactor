<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SemConv\Attributes\CodeAttributes;
use RuntimeException;

class TracerContext
{
    public function __construct(
        private TracerInterface $tracer,
        private ContextInterface $context,
        private ContextStorageInterface $storage,
    ) {
    }

    public function spanBuilder(PreContext $callContext, string $spanName): SpanBuilderInterface
    {
        if ($spanName === '') {
            throw new RuntimeException('span name cannot be empty');
        }
        return $this->tracer->spanBuilder($spanName)
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->setAttribute(CodeAttributes::CODE_FUNCTION_NAME, sprintf('%s::%s', $callContext->class, $callContext->function))
            ->setAttribute(CodeAttributes::CODE_FILE_PATH, $callContext->filename)
            ->setAttribute(CodeAttributes::CODE_LINE_NUMBER, $callContext->function);
    }

    public function storage(): ContextStorageInterface
    {
        return $this->storage;
    }

    public function currentContext(): ContextInterface
    {
        return $this->context;
    }
}
