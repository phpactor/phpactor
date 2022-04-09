<?php

namespace Phpactor\WorseReflection\Tests\SelfTest;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
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

        if ($name !== 'wrAssertType' || $node->argumentExpressionList === null) {
            return $frame;
        }

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

        return $frame;
    }
}
