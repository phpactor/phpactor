<?php

namespace Phpactor\TextDocument\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\Exception\InvalidUriException;
use Phpactor\TextDocument\TextDocumentUri;

class TextDocumentUriTest extends TestCase
{
    public function testCreate(): void
    {
        $uri = TextDocumentUri::fromString('file:///foo/bar.php');
        $this->assertEquals('file:///foo/bar.php', (string) $uri);

        $uri = TextDocumentUri::fromString('file:///C:/foo/bar.php');
        $this->assertEquals('file:///C:/foo/bar.php', (string) $uri);
    }

    public function testFromPhar(): void
    {
        $uri = TextDocumentUri::fromString('phar:///home/daniel/www/phpactor/phpactor/vendor/phpstan/phpstan/phpstan.phar/resources/functionMap.php');
        $this->assertEquals('phar:///home/daniel/www/phpactor/phpactor/vendor/phpstan/phpstan/phpstan.phar/resources/functionMap.php', (string) $uri);
    }

    public function testExceptionOnInvalidFormatUnix(): void
    {
        $this->expectException(InvalidUriException::class);
        TextDocumentUri::fromString('file://foo/bar.php');
    }

    public function testExceptionOnInvalidFormatWindows(): void
    {
        $this->expectException(InvalidUriException::class);
        TextDocumentUri::fromString('file://C:/foo/bar.php');
    }

    public function testCreateUntitled(): void
    {
        $uri = TextDocumentUri::fromString('untitled:Untitled-1');
        $this->assertEquals('untitled:Untitled-1', (string) $uri);
    }

    public function testCreatePhar(): void
    {
        $uri = TextDocumentUri::fromString('phar:///foo/bar.php');
        $this->assertEquals('phar:///foo/bar.php', (string) $uri);
    }

    public function testNormalizesToFileScheme(): void
    {
        $uri = TextDocumentUri::fromString('/foo/bar.php');
        $this->assertEquals('file:///foo/bar.php', (string) $uri);
        $uri = TextDocumentUri::fromString('C:/foo/bar.php');
        $this->assertEquals('file:///C:/foo/bar.php', (string) $uri);
    }

    public function testExceptionOnNonAbsolutePath(): void
    {
        $this->expectException(InvalidUriException::class);
        TextDocumentUri::fromString('i is relative');
    }

    public function testExceptionOnInvalidUri(): void
    {
        $this->expectException(InvalidUriException::class);
        $this->expectExceptionMessage('not parse');
        TextDocumentUri::fromString('');
    }

    public function testExceptionOnNoPath(): void
    {
        $this->expectException(InvalidUriException::class);
        $this->expectExceptionMessage('has no path');
        TextDocumentUri::fromString('file://');
    }

    public function testFromHttpUri(): void
    {
        $this->expectException(InvalidUriException::class);
        $this->expectExceptionMessage('Only "file", "untitled", "phar" schemes are supported, got "http"');
        $uri = TextDocumentUri::fromString('http://foobar/foobar');
    }

    public function testReturnsPath(): void
    {
        $uri = TextDocumentUri::fromString('file:///foo/bar.php');
        $this->assertEquals('/foo/bar.php', $uri->path());
        $uri = TextDocumentUri::fromString('file:///C:/foo/bar.php');
        $this->assertEquals('C:/foo/bar.php', $uri->path());
    }

    public function testScheme(): void
    {
        $uri = TextDocumentUri::fromString('file:///foo/bar.php');
        $this->assertEquals('file', $uri->scheme());
        $uri = TextDocumentUri::fromString('file:///C:/foo/bar.php');
        $this->assertEquals('file', $uri->scheme());
    }
}
