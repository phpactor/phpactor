<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\SourceCode;
use InvalidArgumentException;
use stdClass;

class SourceCodeTest extends TestCase
{
    const SOURCE_CODE = 'source code';
    const EXAMPLE_PATH = '/path/to';

    /**
     * @testdox It can load source code from a file.
     */
    public function testFromPath(): void
    {
        $code = SourceCode::fromPath(__FILE__);
        $this->assertEquals(file_get_contents(__FILE__), (string) $code);
    }

    /**
     * @testdox It throws an exception if file not found.
     */
    public function testFileNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File "Improbable_Path_62.xyz" does not exist');
        SourceCode::fromPath('Improbable_Path_62.xyz');
    }

    public function testFromUnknownReturnsSourceCodeIfGivenSourceCode(): void
    {
        $givenSource = SourceCode::fromString(self::SOURCE_CODE);
        $sourceCode = SourceCode::fromUnknown($givenSource);

        $this->assertSame($givenSource, $sourceCode);
    }

    public function testFromUnknownString(): void
    {
        $sourceCode = SourceCode::fromUnknown(self::SOURCE_CODE);

        $this->assertEquals(SourceCode::fromString(self::SOURCE_CODE), $sourceCode);
    }

    public function testFromTextDocument(): void
    {
        $document = TextDocumentBuilder::create(
            self::SOURCE_CODE
        )->uri(
            self::EXAMPLE_PATH
        )->build();

        $sourceCode = SourceCode::fromUnknown($document);
        $this->assertEquals($sourceCode->__toString(), self::SOURCE_CODE);
        $this->assertEquals($sourceCode->path(), self::EXAMPLE_PATH);
    }

    public function testFromTextDocumentWithNoUri(): void
    {
        $document = TextDocumentBuilder::create(
            self::SOURCE_CODE
        )->build();

        $sourceCode = SourceCode::fromUnknown($document);
        $this->assertEquals($sourceCode->__toString(), self::SOURCE_CODE);
    }
}
