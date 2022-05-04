<?php

namespace Phpactor\WorseReflection\Tests\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\TypeUtil;
use RuntimeException;

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
        $exprs = [];
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $args[] = $resolver->resolveNode($frame, $expression);
            $exprs[] = $expression;
        }

        // get string to compare against
        $expectedType = TypeUtil::valueOrNull($args[0]->type());
        $actualType = $args[1]->type();
        $message = isset($args[2]) ? TypeUtil::valueOrNull($args[2]->type()) : null;
        $position = PositionConverter::intByteOffsetToPosition($node->getStartPosition(), $node->getFileContents());
        if ($actualType->__toString() !== $expectedType) {
            $this->testCase->fail(sprintf(
                '%s: %s is not %s%s on line %s char %s',
                $node->getText(),
                $actualType->__toString(),
                $expectedType,
                $message ? ': ' . $message : '',
                $position->line + 1,
                $position->character + 1,
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

        $actual = $args[1]->symbol()->name();
        $expected = $args[0]->type();
        if (!$expected instanceof StringType) {
            throw new RuntimeException(sprintf('Expected symbol type must be a string got "%s"', $expected->__toString()));
        }
        $message = isset($args[2]) ? TypeUtil::valueOrNull($args[2]->type()) : null;

        if ($expected->value() !== $actual) {
            $this->testCase->fail(sprintf(
                '%s: "%s" is not "%s"',
                $node->getText(),
                $expected,
                $actual
            ));
        }
        $this->testCase->addToAssertionCount(1);
    }
}
