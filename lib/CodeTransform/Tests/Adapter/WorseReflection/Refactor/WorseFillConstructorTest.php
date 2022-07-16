<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Generator;
use GlobIterator;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseFillConstructor;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

class WorseFillConstructorTest extends WorseTestCase
{
    /**
     * @dataProvider provideFill
     */
    public function testFill(
        string $path
    ): void {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset($path);

        $fill = new WorseFillConstructor(
            $this->reflectorForWorkspace($source),
            new Parser(),
        );
        $transformed = $fill->fillConstructor(
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
        foreach ((new GlobIterator(__DIR__ . '/fixtures/fillConstructor*.test')) as $fileInfo) {
            assert($fileInfo instanceof SplFileInfo);
            yield $fileInfo->getBasename() => [
                $fileInfo->getPathname()
            ];
        }
    }
}
