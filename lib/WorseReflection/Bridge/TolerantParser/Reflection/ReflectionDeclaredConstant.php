<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionDeclaredConstant as PhpactorReflectionDeclaredConstant;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;

class ReflectionDeclaredConstant extends AbstractReflectedNode implements PhpactorReflectionDeclaredConstant
{
    private string $name;

    private ArgumentExpression $value;

    public function __construct(
        private ServiceLocator $serviceLocator,
        private TextDocument $sourceCode,
        private CallExpression $node
    ) {
        $this->bindArguments();
    }

    public function name(): Name
    {
        return Name::fromString($this->name);
    }

    public function type(): Type
    {
        return $this->serviceLocator->nodeContextResolver()->resolveNode(new Frame(), $this->value)->type();
    }

    public function sourceCode(): TextDocument
    {
        return $this->sourceCode;
    }

    public function docblock(): DocBlock
    {
        return $this->serviceLocator->docblockFactory()->create($this->node->getLeadingCommentAndWhitespaceText(), $this->scope());
    }

    protected function node(): Node
    {
        return $this->node;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }

    private function bindArguments(): void
    {
        $arguments = $this->node->argumentExpressionList;
        if (!$arguments) {
            return;
        }
        $arguments = iterator_to_array($arguments->getElements());
        if (!is_array($arguments)) {
            return;
        }
        if (isset($arguments[0]) && $arguments[0] instanceof ArgumentExpression) {
            if (!$arguments[0]->expression instanceof StringLiteral) {
                $this->name = '?';
            } else {
                $this->name = $arguments[0]->expression->getStringContentsText();
            }
        }

        if (isset($arguments[1]) && $arguments[1] instanceof ArgumentExpression) {
            $this->value = $arguments[1];
        }
    }
}
