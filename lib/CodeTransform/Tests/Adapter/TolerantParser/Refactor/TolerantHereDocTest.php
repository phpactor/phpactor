<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser\Refactor;

use Generator;
use GlobIterator;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantHereDoc;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

class TolerantHereDocTest extends WorseTestCase
{
    /**
     * @dataProvider provideData
     */
    public function testHereDoc(string $path): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset($path);

        $transformed = $this
            ->createHereDoc($source)
            ->refactor(
                TextDocumentBuilder::create($source)->build(),
                ByteOffset::fromInt($offset),
            )
            ->apply($source);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    public function testOnNonStringObject(): void
    {
        [$source, $offset] = ExtractOffset::fromSource('<?php function (<>){}');

        $hereDoc = $this->createHereDoc($source);
        $edits = $hereDoc->refactor(
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt($offset)
        );

        self::assertCount(0, $edits);
    }

    /**
     * @return Generator<string,array{string}>
     */
    public function provideData(): Generator
    {
        foreach ((new GlobIterator(__DIR__ . '/fixtures/heredoc*.test')) as $fileInfo) {
            assert($fileInfo instanceof SplFileInfo);
            yield $fileInfo->getBasename() => [
                $fileInfo->getPathname()
            ];
        }
    }

    private function createHereDoc(string $source): TolerantHereDoc
    {
        return new TolerantHereDoc(new Parser());
    }
}
