<?php

namespace Phpactor\Filesystem\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\TextDocument\Exception\InvalidUriException;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;

class FilePathTest extends TestCase
{
    #[TestDox('It should throw an exception if the path is not absolute')]
    public function testNotAbsolute(): void
    {
        $this->expectException(InvalidUriException::class);
        $this->expectExceptionMessage('must be absolute');
        FilePath::fromString('foobar');
    }

    public function testFromParts(): void
    {
        $path = FilePath::fromParts(['Hello', 'Goodbye']);
        $this->assertEquals('/Hello/Goodbye', $path->path());
    }

    #[DataProvider('provideFilePathOrString')]
    public function testFromFilePathOrString(FilePath|string $path, string $expectedPath): void
    {
        $filePath = FilePath::fromFilePathOrString($path);
        $this->assertInstanceOf(FilePath::class, $filePath);
        $this->assertEquals($expectedPath, (string) $filePath);
    }

    /**
     * @return Generator<string,array{FilePath|string,string}>
     */
    public static function provideFilePathOrString(): Generator
    {
        yield 'FilePath instance' => [
            FilePath::fromString('/foo.php'),
            '/foo.php'
        ];

        yield 'string' => [
            '/foo.php',
            '/foo.php'
        ];

        yield 'URI string (Unix style)' => [
            'file:///foo.php',
            '/foo.php',
        ];

        yield 'URI string (Windows style)' => [
            'file:///C:/foo.php',
            'C:/foo.php',
        ];

        yield 'PHAR string' => [
            'phar:///foo.php',
            '/foo.php',
        ];
    }

    #[DataProvider('provideUnsupportedInput')]
    public function testThrowExceptionOnUnknowableType(string $input, string $expectedExceptionMessage): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        FilePath::fromString($input);
    }

    /**
     * @return Generator<string,array{string,string}>
     */
    public static function provideUnsupportedInput(): Generator
    {
        yield 'unsupported scheme' => [
            'ftp://host/foo.php',
            'are supported', // only X schemes are supported
        ];

        yield 'URI without a path' => [
            'http://.?x=1&n',
            'are supported', // only X schemes are supported
        ];
    }

    #[TestDox('It generates an absolute path from a relative.')]
    public function testAbsoluteFromString(): void
    {
        $base = FilePath::fromString('/path/to/something');
        $new = $base->makeAbsoluteFromString('else/yes');
        $this->assertEquals('/path/to/something/else/yes', $new->path());
    }

    #[TestDox('If creating a descendant file and the path is absolute and NOT in th
         current branch, an exception should be thrown.')]
    public function testDescendantOutsideOfBranchException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Trying to create descendant');
        $base = FilePath::fromString('/path/to/something');
        $base->makeAbsoluteFromString('/else/yes');
    }

    #[TestDox('If given an absolute path and it lies within the current branch, return new file path.')]
    public function testDescendantInsideOfBranchException(): void
    {
        $base = FilePath::fromString('/path/to/something');
        $path = $base->makeAbsoluteFromString('/path/to/something/yes');
        $this->assertEquals('/path/to/something/yes', (string) $path);
    }

    #[TestDox('It should provide the absolute path.')]
    public function testAbsolute(): void
    {
        $path = FilePath::fromString('/path/to/something/else/yes');
        $this->assertEquals('/path/to/something/else/yes', $path->path());
    }

    #[TestDox('It should return true if it is within another path')]
    public function testWithin(): void
    {
        $path1 = FilePath::fromString('/else/yes');
        $path2 = FilePath::fromString('/else/yes/foobar');

        $this->assertTrue($path2->isWithin($path1));
    }

    #[TestDox('It returns the files extension.')]
    public function itReturnsTheExtension(): void
    {
        $path = FilePath::fromString('/foobar.php');
        $this->assertEquals('php', $path->extension());
    }

    #[TestDox('It returns true or false if it is named a given name.')]
    public function testIsNamed(): void
    {
        $path1 = FilePath::fromString('/else/foobar');
        $path2 = FilePath::fromString('/else/yes/foobar');
        $path3 = FilePath::fromString('/else/yes/brabar');

        $this->assertTrue($path1->isNamed('foobar'));
        $this->assertTrue($path2->isNamed('foobar'));
        $this->assertFalse($path3->isNamed('foobar'));
    }

    public function testAsSplFileInfo(): void
    {
        $path1 = FilePath::fromSplFileInfo(new SplFileInfo((string)TextDocumentUri::fromString(__FILE__)));
        self::assertEquals(Path::canonicalize(__FILE__), $path1->__toString());
        self::assertEquals(Path::canonicalize(__FILE__), $path1->asSplFileInfo()->__toString());
    }
}
