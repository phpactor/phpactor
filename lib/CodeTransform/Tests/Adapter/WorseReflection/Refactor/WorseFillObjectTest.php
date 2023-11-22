<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Generator;
use GlobIterator;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseFillObject;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

class WorseFillObjectTest extends WorseTestCase
{
    /**
     * @dataProvider provideFill
     */
    public function testFill(
        string $path
    ): void {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset($path);

        $fill = $this->createFillObject($source, true, false);
        $transformed = $fill->refactor(
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt($offset)
        )->apply($source);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @dataProvider provideFill
     */
    public function testFillNotNamed(
        string $path
    ): void {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset($path);
        $expected = $this->workspace()->getContents('nonamed');

        $fill = $this->createFillObject($source, false, true);
        $transformed = $fill->refactor(
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt($offset),
        )->apply($source);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    public function testOffsetNotObject(): void
    {
        $fill = $this->createFillObject('');
        $edits = $fill->refactor(
            TextDocumentBuilder::create('<?php echo "hello";')->build(),
            ByteOffset::fromInt(10)
        );
        self::assertCount(0, $edits);
    }

    /**
     * @return Generator<string,array{string}>
     */
    public function provideFill(): Generator
    {
        foreach ((new GlobIterator(__DIR__ . '/fixtures/fillObject*.test')) as $fileInfo) {
            assert($fileInfo instanceof SplFileInfo);
            yield $fileInfo->getBasename() => [
                $fileInfo->getPathname()
            ];
        }
    }

    private function createFillObject(string $source, bool $named = true, bool $hint = false): WorseFillObject
    {
        $fill = new WorseFillObject(
            $this->reflectorForWorkspace($source),
            new Parser(),
            $this->updater(),
            $named,
            $hint
        );
        return $fill;
    }
}
