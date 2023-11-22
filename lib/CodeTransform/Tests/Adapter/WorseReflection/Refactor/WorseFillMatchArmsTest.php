<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Generator;
use GlobIterator;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseFillMatchArms;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

class WorseFillMatchArmsTest extends WorseTestCase
{
    /**
     * @dataProvider provideFill
     */
    public function testFill(
        string $path
    ): void {
        if (!version_compare(PHP_VERSION, '8.1', '>=')) {
            $this->markTestSkipped('Not supported');
        }
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset($path);

        $fill = $this->createRefactor($source);
        $transformed = $fill->refactor(
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt($offset)
        )->apply($source);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return Generator<string,array{string}>
     */
    public function provideFill(): Generator
    {
        foreach ((new GlobIterator(__DIR__ . '/fixtures/fillMatchArms*.test')) as $fileInfo) {
            assert($fileInfo instanceof SplFileInfo);
            yield $fileInfo->getBasename() => [
                $fileInfo->getPathname()
            ];
        }
    }

    private function createRefactor(string $source): WorseFillMatchArms
    {
        $fill = new WorseFillMatchArms(
            $this->reflectorForWorkspace($source),
            new Parser(),
        );
        return $fill;
    }
}
