<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Generator;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateMember;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\TextDocument\TextDocumentBuilder;

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

    /**
     * @return Generator<string, list<string>>
     */
    public function provideExtractMethod(): Generator
    {
        yield 'string' => [ 'generateMethod1.test' ];
        yield 'parameter' => [ 'generateMethod2.test' ];
        yield 'named parameters' => ['generateMethod_namedParams.test'];
        yield 'typed parameter' => [ 'generateMethod3.test' ];
        yield 'undeclared parameter' => [ 'generateMethod4.test' ];
        yield 'expression' => [ 'generateMethod5.test' ];
        yield 'public accessor in another class' => [ 'generateMethod6.test' ];
        yield 'public accessor on interface' => [ 'generateMethod7.test' ];
        yield 'public accessor on interface with namespace' => [ 'generateMethod8.test' ];
        if (version_compare(PHP_VERSION, '8.1', '>=')) {
            yield 'public method on enum' => [ 'generateMethod_enum.test', 'play'];
            yield 'case on enum' => [ 'generateMember_enumCase.test', 'play'];
        }
        yield 'imports classes' => [ 'generateMethod9.test' ];
        yield 'static private method' => [ 'generateMethod10.test' ];
        yield 'static public method' => [ 'generateMethod11.test' ];
        yield 'add return type' => [ 'generateMethod12.test' ];
        yield 'add return type with docblock' => [ 'generateMethod13.test' ];
        yield 'add parameter type multiple literals' => [ 'generateMethod14.test' ];
        yield 'nullable parameter inference' => [ 'generateMethod15.test' ];
        yield 'generic parameter inference' => [ 'generateMethod16.test' ];
        yield 'union false' => [ 'generateMethod17.test' ];
        yield 'duplicated type guesses' => [ 'generateMethod_duplicateNameGuesses.test' ];
        yield 'docblock for complex type' => [ 'generateMethod_complexTypeDocblock.test' ];
        if (version_compare(PHP_VERSION, '8.1', '>=')) {
            yield 'enum' => [ 'generateMethod_enumParams.test' ];
            yield 'backed_enum' => [ 'generateMethod_backedEnumParams.test' ];
        }
    }

    public function testGenerateOnTraitException(): void
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
        $worseSourceCode = TextDocumentBuilder::fromPathAndString('file:///source', $source);
        $reflector = $this->reflectorForWorkspace($worseSourceCode);

        $generateMethod = new WorseGenerateMember(
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
