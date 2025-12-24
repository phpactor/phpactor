<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use GlobIterator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateConstructor;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

class WorseGenerateConstructorTest extends WorseTestCase
{
    #[DataProvider('provideCreate')]
    public function testCreate(
        string $path
    ): void {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset($path);

        $create = $this->generator($source, true, false);
        $textDocumentEditsCollection = $create->generateMethod(
            TextDocumentBuilder::create($source)->uri('file:///foo')->build(),
            ByteOffset::fromInt($offset)
        );

        $transformed = $source;
        foreach ($textDocumentEditsCollection as $textDocumentEdits) {
            $transformed = $textDocumentEdits->textEdits()->apply($transformed);
        }

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return Generator<string,array{string}>
     */
    public static function provideCreate(): Generator
    {
        foreach ((new GlobIterator(__DIR__ . '/fixtures/generateConstructor*.test')) as $fileInfo) {
            assert($fileInfo instanceof SplFileInfo);
            yield $fileInfo->getBasename() => [
                $fileInfo->getPathname()
            ];
        }
    }

    public function testOffsetNotObject(): void
    {
        $create = $this->generator('');
        $edits = $create->generateMethod(
            TextDocumentBuilder::create('<?php echo "hello";')->uri('file:///foo')->build(),
            ByteOffset::fromInt(10)
        );
        self::assertCount(0, $edits);
    }

    private function generator(string $source, bool $named = true, bool $hint = false): WorseGenerateConstructor
    {
        $reflector = $this->reflectorForWorkspace($source);
        return new WorseGenerateConstructor(
            $reflector,
            $this->builderFactory($reflector),
            $this->updater(),
            new TolerantAstProvider()
        );
    }
}
