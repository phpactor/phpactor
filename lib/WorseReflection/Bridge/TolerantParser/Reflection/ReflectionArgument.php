<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Phpactor\WorseReflection\Core\Position;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionArgument as CoreReflectionArgument;
use Phpactor\WorseReflection\Core\Type\AggregateType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\TypeUtil;
use RuntimeException;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;

class ReflectionArgument implements CoreReflectionArgument
{
    public function __construct(
        private ServiceLocator $services,
        private Frame $frame,
        private ArgumentExpression $node
    ) {
    }

    public function guessName(): string
    {
        if ($this->node->expression instanceof Variable) {
            $name = $this->node->expression->name->getText($this->node->getFileContents());

            if (is_string($name) && substr($name, 0, 1) == '$') {
                return substr($name, 1);
            }

            return $name;
        }

        $type = $this->type();

        if (!$type instanceof MissingType) {
            $stringify = function (Type $type) {
                $type = $type->stripNullable();
                if ($type instanceof ClassType) {
                    return lcfirst($type->name->short());
                }
                return lcfirst($type->toPhpString());
            };
            if ($type instanceof AggregateType) {
                return lcfirst(implode('', array_map('ucfirst', array_map($stringify, $type->types))));
            }
            return $stringify($type);
        }


        return 'argument' . $this->index();
    }

    public function type(): Type
    {
        return $this->info()->type();
    }

    public function value()
    {
        return TypeUtil::valueOrNull($this->info()->type());
    }

    public function position(): Position
    {
        return Position::fromFullStartStartAndEnd(
            $this->node->getFullStartPosition(),
            $this->node->getStartPosition(),
            $this->node->getEndPosition()
        );
    }

    private function info(): NodeContext
    {
        return $this->services->symbolContextResolver()->resolveNode($this->frame, $this->node);
    }

    private function index(): int
    {
        $index = 0;

        /** @var ArgumentExpressionList $parent */
        $parent = $this->node->parent;

        foreach ($parent->getElements() as $element) {
            if ($element === $this->node) {
                return $index;
            }
            $index ++;
        }

        throw new RuntimeException(
            'Could not find myself in the list of my parents children'
        );
    }
}
