<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser\Refactor;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantNamedMethodParameters;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;

class TolerantNamedMethodParameterTest extends WorseTestCase
{
    #[DataProvider('provideMethodCallParameters')]
    public function testExtractExpression(string $test): void
    {
        [$source, $expected, $offsetStart] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $refactor = new TolerantNamedMethodParameters(
            $this->reflectorForWorkspace($source),
            new TolerantAstProvider(),
        );
        $edits = $refactor->refactor(SourceCode::fromString($source), ByteOffset::fromInt($offsetStart));
        $transformed = $edits->apply($source);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return Generator<string,array{string}>
     */
    public static function provideMethodCallParameters(): Generator
    {
        yield 'no op' => [ 'methodParametersNoOp.test' ];
        yield 'oneArgument' => [ 'methodParametersOneArgument.test' ];
        yield 'multiple named arguments' => [ 'methodParametersMultipleNamedArguments.test' ];
        yield 'multiple arguments' => [ 'methodParametersMultiplePosArguments.test' ];
        yield 'named arguments skipping' => [ 'methodParametersSkippingNamed.test' ];
        yield 'named arguments wrong order' => [ 'methodParametersWrongOrder.test' ];
        yield 'named arguments with object defaults' => [ 'methodParametersObject.test' ];
    }
}
