<?php

namespace Phpactor\TextDocument\Tests\Benchmark;

use PhpBench\Attributes\BeforeMethods;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\EfficientLineCols;
use Phpactor\TextDocument\LineCol;

#[BeforeMethods("setUp")]
class EfficientLineColsBench
{
    private string $contents;

    public function setUp(): void
    {
        $this->contents = (string)file_get_contents(__DIR__ . '/example/code.test');
    }
    public function benchLineCols(): void
    {
        EfficientLineCols::fromByteOffsetInts(
            $this->contents,
            [89,191,274,326,373,480,552,2000],
        );
    }

    public function benchLineColsUtf16Positions(): void
    {
        EfficientLineCols::fromByteOffsetInts(
            $this->contents,
            [89,191,274,326,373,480,552,2000],
            true
        );
    }

    public function benchIneffificentLineCols(): void
    {
        foreach (
            [89,191,274,326,373,480,552,2000] as $byteOffset
        ) {
            LineCol::fromByteOffset($this->contents, ByteOffset::fromInt($byteOffset));
        }
    }
}
