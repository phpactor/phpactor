<?php

namespace Phpactor\WorseReflection\Tests\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\TypeUtil;

class TestAssertWalker implements Walker
{
    private TestCase $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function nodeFqns(): array
    {
        return [CallExpression::class];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        assert($node instanceof CallExpression);
        $name = $node->callableExpression->getText();

        if ($node->argumentExpressionList === null) {
            return $frame;
        }
        if ($name === 'wrAssertType') {
            $this->assertType($resolver, $frame, $node);
            return $frame;
        }
        if ($name === 'wrAssertEval') {
            $this->assertEval($resolver, $frame, $node);
            return $frame;
        }
        if ($name === 'wrAssertSymbolName') {
            $this->assertSymbolName($resolver, $frame, $node);
            return $frame;
        }

        return $frame;
    }

    private function assertType(FrameResolver $resolver, Frame $frame, Node $node): void
    {
        $list = $node->argumentExpressionList->getElements();
        $args = [];
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $args[] = $resolver->resolveNode($frame, $expression);
        }

        // get string to compare against
        $expectedType = TypeUtil::valueOrNull($args[0]->type());
        $actualType = $args[1]->type();
        $message = isset($args[2]) ? TypeUtil::valueOrNull($args[2]->type()) : null;

        if ($actualType->__toString() !== $expectedType) {
            $this->testCase->fail(sprintf(
                '%s: %s is not %s%s',
                $node->getText(),
                $actualType->__toString(),
                $expectedType,
                $message ? ': ' . $message : '',
            ));
        }
        $this->testCase->addToAssertionCount(1);
    }

    private function assertEval(FrameResolver $resolver, Frame $frame, CallExpression $node): void
    {
        $list = $node->argumentExpressionList->getElements();
        $args = [];
        $toEval = null;
        $resolvedType = new MissingType();
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $toEval = $expression->getText();
            $resolvedType = $resolver->resolveNode($frame, $expression)->type();
            break;
        }

        if ($toEval === null) {
            return;
        }

        $evaled = eval('return ' . $toEval . ';');
        $this->testCase->assertEquals(
            TypeFactory::fromValue($evaled)->__toString(),
            $resolvedType->__toString()
        );
    }

    private function assertSymbolName(FrameResolver $resolver, Frame $frame, CallExpression $node): void
    {
        $list = $node->argumentExpressionList->getElements();
        $args = [];
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $args[] = $resolver->resolveNode($frame, $expression);
        }

        $expectedName = $args[0]->symbol()->name();
        $actualName = $args[1]->type();
        if (!$actualName instanceof StringType) {
            return;
        }
        $message = isset($args[2]) ? TypeUtil::valueOrNull($args[2]->type()) : null;

        if ($actualName->value() !== $expectedName) {
            $this->testCase->fail(sprintf(
                '%s: "%s" is not "%s"',
                $node->getText(),
                $actualName,
                $expectedName
            ));
        }
        $this->testCase->addToAssertionCount(1);
    }
}
