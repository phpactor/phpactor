<?php

namespace Phpactor\CodeTransform\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\SourceCode;
use RuntimeException;

class SourceCodeTest extends TestCase
{
    const PATH = '/bar';
    const SOURCE = 'asd';
    const OTHER_SOURCE = 'other source';
    const OTHER_PATH = '/other/path.php';

    public function testPath(): void
    {
        $source = SourceCode::fromStringAndPath(self::SOURCE, self::PATH);

        $this->assertEquals(self::PATH, $source->uri()?->path());
    }

    public function testFromUnknownReturnsSourceCodeIfPassedSourceCode(): void
    {
        $source1 = SourceCode::fromStringAndPath(self::SOURCE, self::PATH);
        $source2 = SourceCode::fromUnknown($source1);

        $this->assertSame($source1, $source2);
    }

    public function testFromUnknownReturnsSourceCodeIfPassedString(): void
    {
        $source1 = 'hello';
        $source2 = SourceCode::fromUnknown($source1);

        $this->assertEquals(SourceCode::fromString($source1), $source2);
    }

    public function testFromUnknownThrowsExceptionIfTypeIsInvalid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Do not know');
        $source2 = SourceCode::fromUnknown(1234);
    }

    public function testWithSource(): void
    {
        $source1 = SourceCode::fromStringAndPath(self::SOURCE, self::PATH);
        $source2 = $source1->withSource(self::OTHER_SOURCE);

        $this->assertEquals(self::OTHER_SOURCE, $source2->__toString());
        $this->assertNotSame($source1, $source2);
    }

    public function testWithPath(): void
    {
        $source1 = SourceCode::fromStringAndPath(self::SOURCE, self::PATH);
        $source2 = $source1->withPath(self::OTHER_PATH);

        $this->assertEquals(self::OTHER_PATH, $source2->uri()?->path());
        $this->assertNotSame($source1, $source2);
    }

    public function testNonAbsolutePath(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must be absolute');
        SourceCode::fromStringAndPath('asdf', 'path');
    }

    public function testCanonicalizePath(): void
    {
        $sourceCode = SourceCode::fromStringAndPath('asd', '/path/to/here/../');
        $this->assertEquals('/path/to', $sourceCode->uri()?->path());
    }

    public function testExtractSelection(): void
    {
        $sourceCode = SourceCode::fromString('12345678');
        $this->assertEquals('34', $sourceCode->extractSelection(2, 4));
    }

    public function testReplaceSelection(): void
    {
        $sourceCode = SourceCode::fromString('12345678');
        $this->assertEquals('12HE5678', (string) $sourceCode->replaceSelection('HE', 2, 4));
    }
}
