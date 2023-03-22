<?php

namespace Phpactor\WorseReflection\Core\Inference\Visitor;

use Microsoft\PhpParser\Node\Expression\CallExpression;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\NodeContextVisitor;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\TypeUtil;

class TestAssertVisitor implements NodeContextVisitor
{
    private int $assertionCount = 0;

    public function __construct(private TestCase $testCase)
    {
    }

    public function fqns(): array
    {
        return [CallExpression::class];
    }

    public function visit(NodeContext $context): NodeContext
    {
        if (!$context instanceof FunctionCallContext) {
            return $context;
        }

        $name = $context->function()->name()->short();
        $frame = $context->frame() ?? new Frame();

        if ($name === 'wrFrame') {
            dump($frame->__toString());
            return $context;
        }
        if ($name === 'wrAssertType') {
            $this->assertType($context);
            return $context;
        }
        // TODO: These
        //if ($name === 'wrAssertOffset') {
        //    $this->assertOffset($resolver, $frame, $node);
        //    return $context;
        //}
        //if ($name === 'wrReturnType') {
        //    $this->assertReturnType($resolver, $frame, $node);
        //    return $context;
        //}
        //if ($name === 'wrAssertEval') {
        //    $this->assertEval($resolver, $frame, $node);
        //    return $context;
        //}
        //if ($name === 'wrAssertSymbolName') {
        //    $this->assertSymbolName($resolver, $frame, $node);
        //    return $context;
        //}

        return $context;
    }

    public function assertionCount(): int
    {
        return $this->assertionCount;
    }

    private function assertType(FunctionCallContext $context): void
    {
        // get string to compare against
        $expectedType = $context->arguments()->at(0)->type();
        $actualType = $context->arguments()->at(1)->type();
        $this->assertionCount++;
        $this->assertTypeIs($context, $actualType, $expectedType, $context->arguments()->at(2) ??null);
    }

    private function assertTypeIs(NodeContext $functionCallContext, Type $actualType, Type $expectedType, ?NodeContext $message = null): void
    {
        $message = isset($message) ? TypeUtil::valueOrNull($message->type()) : null;
        if ($actualType->__toString() === TypeUtil::valueOrNull($expectedType)) {
            $this->testCase->addToAssertionCount(1);
            return;
        }
        $this->testCase->fail(sprintf(
            "%s: \n\n  %s\n\nis:\n\n  %s\n\non offset %s",
            $message ?: 'Failed asserting that:',
            $actualType->__toString(),
            trim($expectedType->__toString(), '"'),
            $functionCallContext->range()->start()->toInt(),
        ));
    }
}
