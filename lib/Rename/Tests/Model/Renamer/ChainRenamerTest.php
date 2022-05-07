<?php

namespace Phpactor\Rename\Tests\Model\Renamer;

use PHPUnit\Framework\TestCase;
use Phpactor\Rename\Model\LocatedTextEdit;
use Phpactor\Rename\Model\Renamer;
use Phpactor\Rename\Model\Renamer\ChainRenamer;
use Phpactor\Rename\Model\Renamer\InMemoryRenamer;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use function iterator_to_array;

class ChainRenamerTest extends TestCase
{
    public function testReturnsNullWithNoRenamers(): void
    {
        $this->assertResolvesRangeAndResults([], null, []);
    }

    public function testGetFirstNonNullRename(): void
    {
        $range1 = ByteOffsetRange::fromInts(0, 1);
        $results1 = [
            new LocatedTextEdit(TextDocumentUri::fromString('/foo/bar'), TextEdit::create(1, 1, 'foo'))
        ];
        $renamer1 = new InMemoryRenamer($range1, $results1);
        $renamer2 = new InMemoryRenamer(null, []);

        $this->assertResolvesRangeAndResults([$renamer2, $renamer1], $range1, $results1);
        $this->assertResolvesRangeAndResults([$renamer1, $renamer2], $range1, $results1);
    }

    public function testFirstRenameForTwoCapableRenamers(): void
    {
        $range1 = ByteOffsetRange::fromInts(0, 1);
        $range2 = ByteOffsetRange::fromInts(0, 1);
        $results2 = [
            new LocatedTextEdit(TextDocumentUri::fromString('/foo/bar'), TextEdit::create(1, 1, 'foo'))
        ];
        $renamer1 = new InMemoryRenamer($range1, []);
        $renamer2 = new InMemoryRenamer($range2, $results2);

        $this->assertResolvesRangeAndResults([$renamer2, $renamer1], $range2, $results2);
    }

    /**
     * @param Renamer[] $renamers
     * @param array<LocatedTextEdit> $expectedResults
     */
    private function assertResolvesRangeAndResults(
        array $renamers,
        ?ByteOffsetRange $expectedRange,
        array $expectedResults
    ): void {
        $textDocument = TextDocumentBuilder::create('text')->uri('file:///test1')->build();
        $byteOffset = ByteOffset::fromInt(0);

        $this->assertSame(
            $expectedRange,
            $this->createRenamer($renamers)->getRenameRange($textDocument, $byteOffset),
            'Returns expected range',
        );
        $this->assertSame(
            $expectedResults,
            iterator_to_array($this->createRenamer($renamers)->rename($textDocument, $byteOffset, 'foobar')),
            'Returns expected results',
        );
    }

    /**
     * @param Renamer[] $renamers
     */
    private function createRenamer(array $renamers): ChainRenamer
    {
        return new ChainRenamer($renamers);
    }
}
