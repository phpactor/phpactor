<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Helper;

use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\WorseInterestingOffsetFinder;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Inference\Symbol;

class WorseInterestingOffsetFinderTest extends WorseTestCase
{
    /**
     * @dataProvider provideFindSomethingInterestingWhen
     */
    public function testFindSomethingIterestingWhen(string $source, string $expectedSymbolType): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $reflector = $this->reflectorForWorkspace($source);
        $document = TextDocumentBuilder::create($source)->build();
        $offset = ByteOffset::fromInt($offset);

        $newOffset = (new WorseInterestingOffsetFinder($reflector))->find($document, $offset);
        $reflectionOffset = $reflector->reflectOffset($document, $newOffset);

        $this->assertEquals($expectedSymbolType, $reflectionOffset->nodeContext()->symbol()->symbolType());
    }

    public function provideFindSomethingInterestingWhen()
    {
        yield 'offset in empty file' => [
            <<<'EOT'
                <?php

                <>
                EOT
            , Symbol::UNKNOWN,
        ];

        yield 'offset over target class' => [
            <<<'EOT'
                <?php

                class F<>oobar
                {
                    }
                EOT
            , Symbol::CLASS_,
        ];

        yield 'offset in whitespace in target class' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                <>
                }
                EOT
            , Symbol::CLASS_,
            ];

        yield 'offset on method' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function <>methodOne()
                    {
                    }
                }
                EOT
            , Symbol::METHOD,
        ];

        yield 'offset in method' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function methodOne()
                    {
                        <>
                    }
                }
                EOT
            , Symbol::METHOD,
            ];

        yield 'offset in method call' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function methodOne()
                    {
                        $this->ba<>r();
                    }

                    private function bar()
                    {
                    }
                }
                EOT
            , Symbol::METHOD,
        ];


        yield 'offset on var' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function methodOne()
                    {
                        $fo<>o;
                    }
                }
                EOT
            , Symbol::VARIABLE,
        ];

        yield 'offset on expression' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function methodOne()
                    {
                        $foo = $bar + 3 / 2 + $<>foo;
                    }
                }
                EOT
            , Symbol::VARIABLE,
        ];
    }
}
