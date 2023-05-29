<?php

namespace Phpactor\TextDocument\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\Exception\InvalidUriException;
use Phpactor\TextDocument\TextDocumentUri;

class TextDocumentUriTest extends TestCase
{
    public function testCreate(): void
    {
        $uri = TextDocumentUri::fromString('file://' . __FILE__);
        $this->assertEquals('file://' . __FILE__, (string) $uri);
    }

    // TODO: This doesn't work
    //public function testCreateUntitled(): void
    //{
    //    $uri = TextDocumentUri::fromString('file://' . __FILE__);
    //    $this->assertEquals('file://' . __FILE__, (string) $uri);
    //}

    public function testNormalizesToFileScheme(): void
    {
        $uri = TextDocumentUri::fromString(__FILE__);
        $this->assertEquals('file://' . __FILE__, (string) $uri);
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

    public function testFromPath(): void
    {
        $uri = TextDocumentUri::fromString('/foobar');
        $this->assertEquals('file:///foobar', $uri->__toString());
    }

    public function testFromHttpUri(): void
    {
        $this->expectException(InvalidUriException::class);
        $this->expectExceptionMessage('Only "file://" scheme is supported, got "http"');
        $uri = TextDocumentUri::fromString('http://foobar/foobar');
    }


    public function testReturnsPath(): void
    {
        $uri = TextDocumentUri::fromString('file://' . __FILE__);
        $this->assertEquals(__FILE__, $uri->path());
    }

    public function testScheme(): void
    {
        $uri = TextDocumentUri::fromString('file://' . __FILE__);
        $this->assertEquals('file', $uri->scheme());
    }
}
