<?php

namespace Phpactor\CodeTransform\Tests\Unit;

use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Prophecy\Argument;
use Phpactor\CodeTransform\Domain\Transformers;

class CodeTransformTest extends TestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    /**
     * @testdox It should apply the given transformers to source code.
     */
    public function testApplyTransformers(): void
    {
        $expectedCode = SourceCode::fromString('hello goodbye');
        $trans1 = $this->prophesize(Transformer::class);
        $trans1->transform(Argument::type(SourceCode::class))->willReturn(new Success(TextEdits::one(
            TextEdit::create(ByteOffset::fromInt(5), 0, ' goodbye')
        )));

        $code = $this->create([
            'one' => $trans1->reveal()
        ])->transform('hello', [ 'one' ]);

        $this->assertEquals($expectedCode, $code);
    }

    public function testAcceptsSourceCodeAsParameter(): void
    {
        $expectedCode = SourceCode::fromStringAndPath('hello goodbye', '/path/to');

        $trans1 = $this->prophesize(Transformer::class);
        $trans1->transform($expectedCode)->willReturn(new Success(TextEdits::none()));

        $code = $this->create([
            'one' => $trans1->reveal()
        ])->transform($expectedCode, [ 'one' ]);

        $this->assertEquals($expectedCode, $code);
    }


    public function create(array $transformers): CodeTransform
    {
        /** @phpstan-ignore-next-line */
        return CodeTransform::fromTransformers(Transformers::fromArray($transformers));
    }
}
