<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;

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
        dd('fuck');
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
        $list = $node->argumentExpressionList->getElements();
        $args = [];
        $exprs = [];
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $args[] = $resolver->resolveNode($frame, $expression);
            $exprs[] = $expression;
        }

        // get string to compare against
        $expectedType = $args[0]->type();
        $actualType = $args[1]->type();
        $this->assertionCount++;
        $this->assertTypeIs($node, $actualType, $expectedType, $args[2]??null);
    }
}
