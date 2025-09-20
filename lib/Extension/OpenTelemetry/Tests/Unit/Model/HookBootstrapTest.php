<?php

namespace Phpactor\Extension\OpenTelemetry\Tests\Unit\Model;

use Generator;
use OpenTelemetry\API\Trace\TracerInterface;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\OpenTelemetry\Model\PreContext;
use Phpactor\Extension\OpenTelemetry\Model\ClassHook;
use Phpactor\Extension\OpenTelemetry\Model\HookBootstrap;
use Phpactor\Extension\OpenTelemetry\Model\HookProvider;
use Phpactor\Extension\OpenTelemetry\Model\PostContext;

final class HookBootstrapTest extends TestCase
{
    public function testBoostrap(): void
    {
        $bootstrap = new HookBootstrap([new TestProvider()]);
        ($bootstrap)->bootstrap();
        self::assertTrue($bootstrap->initialized);
    }
}

class TestProvider implements HookProvider {
    public function hooks(): Generator
    {
        yield new ClassHook(
            self::class,
            'testBootstrap',
            function (TracerInterface $tracer, PreContext $context) {
            },
            function (TracerInterface $tracer, PostContext $context) {
            },
        );
    }

}
