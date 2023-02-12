<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\CodeBuilder\Tests\Unit\Util\TextFormatTest;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeTransform\Adapter\DocblockParser\ParserDocblockUpdater;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\WorseReflection\Core\SourceCode as WorseSourceCode;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;

class WorseGenerateMethodTest extends WorseTestCase
{
    /**
     * @dataProvider provideExtractMethod
     */
    public function testGenerateMethod(string $test, ?string $name = null): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $transformed = $this->generateMethod($source, $offset, $name);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    public function provideExtractMethod(): array
    {
        return [
            'string' => [
                'generateMethod1.test',
            ],
            'parameter' => [
                'generateMethod2.test',
            ],
            'typed parameter' => [
                'generateMethod3.test',
            ],
            'undeclared parameter' => [
                'generateMethod4.test',
            ],
            'expression' => [
                'generateMethod5.test',
            ],
            'public accessor in another class' => [
                'generateMethod6.test',
            ],
            'public accessor on interface' => [
                'generateMethod7.test',
            ],
            'public accessor on interface with namespace' => [
                'generateMethod8.test',
            ],
            'imports classes' => [
                'generateMethod9.test',
            ],
            'static private method' => [
                'generateMethod10.test',
            ],
            'static public method' => [
                'generateMethod11.test',
            ],
            'add return type' => [
                'generateMethod12.test',
            ],
            'add return type with docblock' => [
                'generateMethod13.test',
            ],
            'add param type multiple literals' => [
                'generateMethod14.test',
            ],
            'nullable parameter inference' => [
                'generateMethod15.test',
            ],
            'generic parameter inference' => [
                'generateMethod16.test',
            ],
            'union false' => [
                'generateMethod17.test',
            ],
            'duplicated type guesses' => [
                'generateMethod_duplicateNameGuesses.test',
            ],
            'docblock for complex type' => [
                'generateMethod_complexTypeDocblock.test',
            ],
        ];
    }

    public function testGenerateOnNonClassInterfaceException(): void
    {
        $this->expectException(TransformException::class);
        $this->expectExceptionMessage('Can only generate methods on classes');
        $source = <<<'EOT'
            <?php
            trait Hello
            {
            }

            class Goodbye
            {
                /**
                 * @var Hello
                 */
                private $hello;

                public function greet()
                {
                    $this->hello->asd();
                }
            }
            EOT
        ;

        $this->generateMethod($source, 152, 'test_name');
    }

    private function generateMethod(string $source, int $start, ?string $name): string
    {
        $worseSourceCode = WorseSourceCode::fromPathAndString('file:///source', $source);
        $reflector = $this->reflectorForWorkspace($worseSourceCode);

        $generateMethod = new WorseGenerateMethod(
            $reflector,
            new WorseBuilderFactory($reflector),
            $this->updater()
        );
        $sourceCode = SourceCode::fromStringAndPath($source, 'file:///source');
        $textDocumentEdits = $generateMethod->generateMethod($sourceCode, $start, $name);

        $transformed = SourceCode::fromStringAndPath(
            (string) $textDocumentEdits->textEdits()->apply($sourceCode),
            $textDocumentEdits->uri()->path()
        );
        return $transformed;
    }
}
