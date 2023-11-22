<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Generator;
use GlobIterator;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseFillMatchArms;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseFillObject;
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
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset($path);

        $fill = $this->createFillObject($source, true, false);
        $transformed = $fill->fillObject(
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

    private function createFillObject(string $source, bool $named = true, bool $hint = false): WorseFillMatchArms
    {
        $fill = new WorseFillMatchArms(
            $this->reflectorForWorkspace($source),
            new Parser(),
            $this->updater(),
            $named,
            $hint
        );
        return $fill;
    }
}
