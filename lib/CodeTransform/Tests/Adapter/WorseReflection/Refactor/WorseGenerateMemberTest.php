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
     * @dataProvider provideGenerateMember
     */
    public function testGenerateMethod(string $test, ?string $name = null): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $transformed = $this->generateMember($source, $offset, $name);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return Generator<string, list<string>>
     */
    public function provideGenerateMember(): Generator
    {
        yield 'string' => [ 'generateMember1.test' ];
        yield 'parameter' => [ 'generateMember2.test' ];
        yield 'named parameters' => ['generateMember_namedParams.test'];
        yield 'typed parameter' => [ 'generateMember3.test' ];
        yield 'undeclared parameter' => [ 'generateMember4.test' ];
        yield 'expression' => [ 'generateMember5.test' ];
        yield 'public accessor in another class' => [ 'generateMember6.test' ];
        yield 'public accessor on interface' => [ 'generateMember7.test' ];
        yield 'public accessor on interface with namespace' => [ 'generateMember8.test' ];
        yield 'imports classes' => [ 'generateMember9.test' ];
        yield 'static private method' => [ 'generateMember10.test' ];
        yield 'static public method' => [ 'generateMember11.test' ];
        yield 'add return type' => [ 'generateMember12.test' ];
        yield 'add return type with docblock' => [ 'generateMember13.test' ];
        yield 'add parameter type multiple literals' => [ 'generateMember14.test' ];
        yield 'nullable parameter inference' => [ 'generateMember15.test' ];
        yield 'generic parameter inference' => [ 'generateMember16.test' ];
        yield 'union false' => [ 'generateMember17.test' ];
        yield 'duplicated type guesses' => [ 'generateMember_duplicateNameGuesses.test' ];
        yield 'docblock for complex type' => [ 'generateMember_complexTypeDocblock.test' ];
        if (version_compare(PHP_VERSION, '8.1', '>=')) {
            yield 'enum' => [ 'generateMember_enumParams.test' ];
            yield 'backed_enum' => [ 'generateMember_backedEnumParams.test' ];
            yield 'public method on enum' => [ 'generateMember_enum.test', 'play'];
            yield 'case on enum' => [ 'generateMember_enumCase.test', 'Foo'];
        }
        yield 'private constant on class' => [ 'generateMember_constant.test', 'FOO'];
        yield 'public constant on class' => [ 'generateMember_constantPublic.test', 'FOO'];
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

        $this->generateMember($source, 152, 'test_name');
    }

    private function generateMember(string $source, int $start, ?string $name): string
    {
        $worseSourceCode = TextDocumentBuilder::fromPathAndString('file:///source', $source);
        $reflector = $this->reflectorForWorkspace($worseSourceCode);

        $generateMember = new WorseGenerateMember(
            $reflector,
            new WorseBuilderFactory($reflector),
            $this->updater()
        );
        $sourceCode = SourceCode::fromStringAndPath($source, 'file:///source');
        $textDocumentEdits = $generateMember->generateMember($sourceCode, $start, $name);

        $transformed = SourceCode::fromStringAndPath(
            (string) $textDocumentEdits->textEdits()->apply($sourceCode),
            $textDocumentEdits->uri()->path()
        );
        return $transformed;
    }
}
