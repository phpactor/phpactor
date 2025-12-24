<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use GlobIterator;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseFillMatchArms;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

class WorseFillMatchArmsTest extends WorseTestCase
{
    #[DataProvider('provideFill')]
    public function testFill(
        string $path
    ): void {
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
    public static function provideFill(): Generator
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
            new \Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider(),
        );
        return $fill;
    }
}
