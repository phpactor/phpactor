<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Util;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use RuntimeException;

class OffsetExtractorTest extends TestCase
{
    public function testOffset(): void
    {
        $extractor = OffsetExtractor::create()
            ->registerOffset('selection', '<>')
            ->parse('Test string with<> selector');

        $selection = $extractor->offset('selection');
        $newSource = $extractor->source();
        
        $this->assertEquals(ByteOffset::fromInt(16), $selection);
        $this->assertEquals('Test string with selector', $newSource);
    }

    public function testFirstOffset(): void
    {
        $extractor = OffsetExtractor::create()
            ->registerOffset('selection', '<>')
            ->parse('Test string with<> selector');

        $selection = $extractor->offset();
        $newSource = $extractor->source();
        
        $this->assertEquals(ByteOffset::fromInt(16), $selection);
        $this->assertEquals('Test string with selector', $newSource);
    }

    public function testPreservesMultibyteOffset(): void
    {
        $extractor = OffsetExtractor::create()
            ->registerOffset('selection', '<>')
            ->parse('Test string 🐱   <> selector');

        $selection = $extractor->offset('selection');
        $newSource = $extractor->source();
        
        $this->assertEquals(ByteOffset::fromInt(19), $selection);
        $this->assertEquals('Test string 🐱    selector', $newSource);
    }

    public function testExceptionWhenNoOffsetIsFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No "selection" offsets found');

        $extractor = OffsetExtractor::create()
            ->registerOffset('selection', '<>')
            ->parse('Test string without selector');

        $extractor->offset('selection');
    }

    public function testExceptionWhenNoOffsetIsRegistered(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No offset registered');

        $extractor = OffsetExtractor::create()
            ->parse('Test string without selector');

        $extractor->offset('selection');
    }

    public function testOffsets(): void
    {
        $extractor = OffsetExtractor::create()
            ->registerOffset('selection', '<>')
            ->parse('Test string with<> two select<>ors');
        $selection = $extractor->offsets('selection');
        $newSource = $extractor->source();
        $this->assertEquals([
            ByteOffset::fromInt(16),
            ByteOffset::fromInt(27)
        ], $selection);
        $this->assertEquals('Test string with two selectors', $newSource);
    }

    public function testAllOffsets(): void
    {
        $extractor = OffsetExtractor::create()
            ->registerOffset('selection', '<>')
            ->parse('Test string with<> two select<>ors');
        $selection = $extractor->offsets();
        $newSource = $extractor->source();
        $this->assertEquals([
            ByteOffset::fromInt(16),
            ByteOffset::fromInt(27)
        ], $selection);
        $this->assertEquals('Test string with two selectors', $newSource);
    }
    
    public function testRange(): void
    {
        $extractor = OffsetExtractor::create()
            ->registerRange('textEdit', '{{', '}}')
            ->parse('Test string {{with}} selector');

        $textEdit = $extractor->range('textEdit');
        $newSource = $extractor->source();

        $this->assertEquals(ByteOffsetRange::fromInts(12, 16), $textEdit);
        $this->assertEquals('Test string with selector', $newSource);
    }

    public function testFirstRange(): void
    {
        $extractor = OffsetExtractor::create()
            ->registerRange('textEdit', '{{', '}}')
            ->parse('Test string {{with}} selector');

        $textEdit = $extractor->range();
        $newSource = $extractor->source();

        $this->assertEquals(ByteOffsetRange::fromInts(12, 16), $textEdit);
        $this->assertEquals('Test string with selector', $newSource);
    }

    public function testRanges(): void
    {
        $extractor = OffsetExtractor::create()
            ->registerRange('textEdit', '{{', '}}')
            ->parse('Test string {{with}} two {{selectors}}');
        $textEdit = $extractor->ranges('textEdit');
        $newSource = $extractor->source();
        $this->assertEquals(
            [
                ByteOffsetRange::fromInts(12, 16),
                ByteOffsetRange::fromInts(21, 30),
            ],
            $textEdit
        );
        $this->assertEquals('Test string with two selectors', $newSource);
    }

    public function testReturnsAllRanges(): void
    {
        $extractor = OffsetExtractor::create()
            ->registerRange('textEdit', '{{', '}}')
            ->parse('Test string {{with}} two {{selectors}}');
        $textEdit = $extractor->ranges();
        $newSource = $extractor->source();
        $this->assertEquals(
            [
                ByteOffsetRange::fromInts(12, 16),
                ByteOffsetRange::fromInts(21, 30),
            ],
            $textEdit
        );
        $this->assertEquals('Test string with two selectors', $newSource);
    }

    public function testExceptionWhenNoRangeIsFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No "selection" ranges found');

        $extractor = OffsetExtractor::create()
            ->registerRange('selection', '<', '>')
            ->parse('Test string without selector');

        $extractor->range('selection');
    }

    public function testExceptionWhenNoRangeIsRegistered(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No range registered');

        $extractor = OffsetExtractor::create()
            ->parse('Test string without selector');

        $extractor->range('selection');
    }
}
