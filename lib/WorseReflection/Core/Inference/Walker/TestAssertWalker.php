<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\IntLiteralType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;
use Phpactor\WorseReflection\TypeUtil;
use RuntimeException;

class TestAssertWalker implements Walker
{
    public function __construct(private TestCase $testCase)
    {
    }

    public function nodeFqns(): array
    {
        return [CallExpression::class];
    }

    public function enter(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        assert($node instanceof CallExpression);
        $name = $node->callableExpression->getText();

        if ($name === 'wrFrame') {
            dump($frameStack->current()->__toString());
            return;
        }
        if ($node->argumentExpressionList === null) {
            return;
        }
        if ($name === 'wrAssertType') {
            $this->assertType($resolver, $frameStack, $node);
            return;
        }
        if ($name === 'wrAssertOffset') {
            $this->assertOffset($resolver, $frameStack, $node);
            return;
        }
        if ($name === 'wrReturnType') {
            $this->assertReturnType($resolver, $frameStack, $node);
            return;
        }
        if ($name === 'wrAssertEval') {
            $this->assertEval($resolver, $frameStack, $node);
            return;
        }
        if ($name === 'wrAssertSymbolName') {
            $this->assertSymbolName($resolver, $frameStack, $node);
            return;
        }

        return;
    }

    public function exit(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        return;
    }

    private function assertType(FrameResolver $resolver, FrameStack $frameStack, CallExpression $node): void
    {
        $list = $node->argumentExpressionList->getElements();
        $args = [];
        $exprs = [];
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $args[] = $resolver->resolveNode($frameStack, $expression);
            $exprs[] = $expression;
        }

        // get string to compare against
        $expectedType = $args[0]->type();
        $actualType = $args[1]->type();
        $this->assertTypeIs($node, $actualType, $expectedType, $args[2]??null);
    }

    private function assertEval(FrameResolver $resolver, FrameStack $frameStack, CallExpression $node): void
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
            $resolvedType = $resolver->resolveNode($frameStack, $expression)->type();
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

    private function assertSymbolName(FrameResolver $resolver, FrameStack $frameStack, CallExpression $node): void
    {
        $argList = $node->argumentExpressionList;
        $args = $this->resolveArgs($argList, $resolver, $frame);

        $actual = $args[1]->symbol()->name();
        $expected = $args[0]->type();
        if (!$expected instanceof StringLiteralType) {
            throw new RuntimeException(sprintf('Expected symbol type must be a string got "%s"', $expected->__toString()));
        }
        $message = isset($args[2]) ? TypeUtil::valueOrNull($args[2]->type()) : null;

        if ($expected->value() !== $actual) {
            $this->testCase->fail(sprintf(
                "%s:\n  %s\nis not\n  %s",
                $node->getText(),
                $expected,
                $actual
            ));
        }
        $this->testCase->addToAssertionCount(1);
    }

    private function assertReturnType(FrameResolver $resolver, FrameStack $frameStack, CallExpression $node): void
    {
        $returnType = $frame->returnType();
        $args = $this->resolveArgs($node->argumentExpressionList, $resolver, $frame);
        if (!isset($args[0])) {
            throw new RuntimeException(
                'wrAssertReturnType requires an expected type argument'
            );
        }
        $expected = $args[0]->type();
        if (!$expected instanceof StringLiteralType) {
            throw new RuntimeException(sprintf('Expected symbol type must be a string got "%s"', $expected->__toString()));
        }


        $this->assertTypeIs($node, $frame->returnType(), $expected);
    }

    private function assertOffset(FrameResolver $resolver, FrameStack $frameStack, CallExpression $node): void
    {
        $args = $this->resolveArgs($node->argumentExpressionList, $resolver, $frame);
        $expectedType = $args[0]->type();
        $type = $args[1]->type();
        if (!$type instanceof IntLiteralType) {
            throw new RuntimeException(
                'Expected int literal'
            );
        }
        $offset = $resolver->reflector()->reflectOffset($node->getFileContents(), $type->value());
        $this->assertTypeIs($node, $offset->nodeContext()->type(), $expectedType);
    }

    /**
     * @return array<int,NodeContext>
     */
    private function resolveArgs(?ArgumentExpressionList $argList, FrameResolver $resolver, FrameStack $frameStack): array
    {
        $list = $argList->getElements();
        $args = [];
        foreach ($list as $expression) {
            if (!$expression instanceof ArgumentExpression) {
                continue;
            }

            $args[] = $resolver->resolveNode($frameStackStack, $expression);
        }
        return $args;
    }

    private function assertTypeIs(Node $node, Type $actualType, Type $expectedType, ?NodeContext $message = null): void
    {
        $message = isset($message) ? TypeUtil::valueOrNull($message->type()) : null;
        $position = PositionConverter::intByteOffsetToPosition($node->getStartPosition(), $node->getFileContents());
        if ($actualType->__toString() === TypeUtil::valueOrNull($expectedType)) {
            $this->testCase->addToAssertionCount(1);
            return;
        }
        $this->testCase->fail(sprintf(
            "%s: \n\n  %s\n\nis:\n\n  %s\n\non offset %s line %s char %s",
            $message ?: 'Failed asserting that:',
            $actualType->__toString(),
            trim($expectedType->__toString(), '"'),
            $node->getStartPosition(),
            $position->line + 1,
            $position->character + 1,
        ));
    }
}
