<?php

namespace Phpactor\TextDocument\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocumentBuilder;
use Symfony\Component\Filesystem\Path;

class TextDocumentBuilderTest extends TestCase
{
    const EXAMPLE_TEXT = 'hello world';
    const EXAMPLE_URI = 'file:///path/to';

    public function testCreate(): void
    {
        $doc = TextDocumentBuilder::create(self::EXAMPLE_TEXT)->language('php')->uri(self::EXAMPLE_URI)->build();
        $this->assertEquals(self::EXAMPLE_URI, $doc->uri()->__toString());
        $this->assertEquals(self::EXAMPLE_TEXT, $doc->__toString());
        $this->assertEquals('php', $doc->language());
    }

    public function testFromUri(): void
    {
        $doc = TextDocumentBuilder::fromUri('file://' . __FILE__)->build();
        $this->assertEquals('file://' . Path::canonicalize(__FILE__), $doc->uri());
        $this->assertEquals(file_get_contents(__FILE__), $doc->__toString());
    }

    public function testFromTextDocument(): void
    {
        $doc = TextDocumentBuilder::fromTextDocument(
            TextDocumentBuilder::create('foobar')
                ->uri('file:///foobar/asd')
                ->language('foo')->build()
        )->build();

        $this->assertEquals('foobar', $doc->__toString());
        $this->assertEquals('file:///foobar/asd', $doc->uri()->__toString());
        $this->assertEquals('/foobar/asd', $doc->uri()?->path());
        $this->assertEquals('foo', $doc->language()->__toString());
    }

    public function testExceptionOnNotExists(): void
    {
        $this->expectException(TextDocumentNotFound::class);
        TextDocumentBuilder::fromUri('file:///no-existy');
    }
}
