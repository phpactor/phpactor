<?php

namespace Phpactor\Extension\OpenTelemetry\Tests\Unit\Model;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\OpenTelemetry\Model\PreContext;
use Phpactor\Extension\OpenTelemetry\Model\ClassHook;
use Phpactor\Extension\OpenTelemetry\Model\HookBootstrap;
use Phpactor\Extension\OpenTelemetry\Model\HookProvider;
use Phpactor\Extension\OpenTelemetry\Model\TracerContext;

final class HookBootstrapTest extends TestCase
{
    public function testBoostrap(): void
    {
        if (!extension_loaded('opentelemetry')) {
            $this->markTestSkipped('Requires opentelemetry extension');
        }
        $bootstrap = new HookBootstrap([new TestProvider()]);
        ($bootstrap)->bootstrap();
        self::assertTrue($bootstrap->initialized);
        $class = new ExampleClass();
        $class->foo();
    }

    public function hookTest(): void
    {
    }
}

class ExampleClass
{
    public function foo(): void
    {
    }
}

class TestProvider implements HookProvider
{
    public function hooks(): Generator
    {
        yield new ClassHook(
            ExampleClass::class,
            'foo',
            function (TracerContext $tracer, PreContext $context) {
                return $tracer->spanBuilder($context, 'test')->startSpan();
            },
        );
    }
}
