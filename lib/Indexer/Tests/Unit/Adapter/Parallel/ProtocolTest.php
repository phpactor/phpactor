<?php

namespace Phpactor\Indexer\Tests\Unit\Adapter\Parallel;

use Phpactor\Indexer\Adapter\Parallel\Protocol;
use PHPUnit\Framework\TestCase;

class ProtocolTest extends TestCase
{
    public function testDecodesAnEncodedFrame(): void
    {
        $buffer = Protocol::encode(Protocol::FRAME_JOB, 'hello');

        self::assertEquals([[Protocol::FRAME_JOB, 'hello']], Protocol::decode($buffer));
        self::assertEquals('', $buffer);
    }

    public function testDecodesSeveralFramesAtOnce(): void
    {
        $buffer =
            Protocol::encode(Protocol::FRAME_RECORDS, 'one') .
            Protocol::encode(Protocol::FRAME_RECORDS, 'two');

        self::assertEquals([
            [Protocol::FRAME_RECORDS, 'one'],
            [Protocol::FRAME_RECORDS, 'two'],
        ], Protocol::decode($buffer));
    }

    public function testLeavesAnIncompleteFrameInTheBuffer(): void
    {
        $frame = Protocol::encode(Protocol::FRAME_RECORDS, 'abcdef');
        $buffer = substr($frame, 0, -2);

        self::assertEquals([], Protocol::decode($buffer));

        $buffer .= substr($frame, -2);

        self::assertEquals([[Protocol::FRAME_RECORDS, 'abcdef']], Protocol::decode($buffer));
    }

    public function testHandlesPayloadsContainingNewlinesAndNulBytes(): void
    {
        $payload = serialize(["one\ntwo", "three\0four"]);
        $buffer = Protocol::encode(Protocol::FRAME_RECORDS, $payload);

        self::assertEquals([[Protocol::FRAME_RECORDS, $payload]], Protocol::decode($buffer));
        self::assertEquals('', $buffer);
    }
}
