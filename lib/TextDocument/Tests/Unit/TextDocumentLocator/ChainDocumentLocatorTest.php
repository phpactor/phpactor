<?php

namespace Phpactor\TextDocument\Tests\Unit\TextDocumentLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextDocumentLocator\ChainDocumentLocator;
use Phpactor\TextDocument\TextDocumentLocator\InMemoryDocumentLocator;

class ChainDocumentLocatorTest extends TestCase
{
    public function testThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(TextDocumentNotFound::class);
        $this->createWorkspace()->get(TextDocumentUri::fromString('file:///foobar'));
    }

    public function testReturnsTextDocument(): void
    {
        $document = TextDocumentBuilder::create('foobar')->uri('/path/to/foo')->build();

        self::assertSame(
            $document,
            $this->createWorkspace([
                InMemoryDocumentLocator::fromTextDocuments([
                    $document
                ])
            ])->get(TextDocumentUri::fromString('file:///path/to/foo'))
        );
    }

    public function testReturnsTextDocumentFromFirstWorkspace(): void
    {
        $document1 = TextDocumentBuilder::create('one')->uri('/path/to/foo')->build();
        $document2 = TextDocumentBuilder::create('two')->uri('/path/to/foo')->build();

        self::assertSame(
            $document1,
            $this->createWorkspace([
                InMemoryDocumentLocator::fromTextDocuments([
                    $document1
                ]),
                InMemoryDocumentLocator::fromTextDocuments([
                    $document2
                ])
            ])->get(TextDocumentUri::fromString('file:///path/to/foo'))
        );
    }

    /**
     * @param TextDocumentLocator[] $workspaces
     */
    private function createWorkspace(array $workspaces = []): ChainDocumentLocator
    {
        return new ChainDocumentLocator($workspaces);
    }
}
