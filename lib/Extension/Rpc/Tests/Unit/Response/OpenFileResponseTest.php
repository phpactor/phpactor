<?php

namespace Phpactor\Extension\Rpc\Tests\Unit\Response;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use RuntimeException;

class OpenFileResponseTest extends TestCase
{
    public function testReturnsDefaultTarget(): void
    {
        $response = OpenFileResponse::fromPath(__FILE__);
        $this->assertEquals(OpenFileResponse::TARGET_FOCUSED_WINDOW, $response->target());
    }

    public function testExceptionOnInvalidTarget(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown target "nope"');
        OpenFileResponse::fromPath(__FILE__)
            ->withTarget('nope');
    }

    public function testReturnsConfiguredTarget(): void
    {
        $response = OpenFileResponse::fromPath(__FILE__)
            ->withTarget(OpenFileResponse::TARGET_HORIZONTAL_SPLIT);
        $this->assertEquals(OpenFileResponse::TARGET_HORIZONTAL_SPLIT, $response->target());
    }

    public function testReturnsParameters(): void
    {
        $response = OpenFileResponse::fromPathAndOffset(__FILE__, 6)
            ->withTarget(OpenFileResponse::TARGET_HORIZONTAL_SPLIT)
            ->withForcedReload(true);
        $this->assertEquals([
            'path' => __FILE__,
            'offset' => 6,
            'force_reload' => true,
            'target' => OpenFileResponse::TARGET_HORIZONTAL_SPLIT
        ], $response->parameters());
    }
}
