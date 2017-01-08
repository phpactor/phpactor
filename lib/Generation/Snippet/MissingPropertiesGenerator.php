<?php

namespace Phpactor\Generation\Snippet;

use BetterReflection\Reflector\ClassReflector;
use Phpactor\CodeContext;
use Phpactor\Util\ClassUtil;
use BetterReflection\Reflection\ReflectionMethod;
use Phpactor\Generation\SnippetGeneratorInterface;
use PhpParser\NodeTraverser;
use Phpactor\AstVisitor\AssignedPropertiesVisitor;
use PhpParser\Node;
use BetterReflection\Util\Visitor\VariableCollectionVisitor;
use BetterReflection\NodeCompiler\CompilerContext;
use BetterReflection\Reflection\ReflectionClass;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Scalar\DNumber;

class MissingPropertiesGenerator implements SnippetGeneratorInterface
{
    /**
     * @var ClassReflector
     */
    private $classReflector;

    /**
     * @var ClassUtil
     */
    private $classUtil;

    /**
     * @var AssignedPropertiesVisitor
     */
    private $assignedPropertiesVisitor;

    public function __construct(
        ClassReflector $classReflector,
        ClassUtil $classUtil,
        AssignedPropertiesVisitor $assignedPropertiesVisitor = null
    )
    {
        $this->classReflector = $classReflector;
        $this->classUtil = $classUtil;
        $this->assignedPropertiesVisitor = $assignedPropertiesVisitor ?: new AssignedPropertiesVisitor();
    }

    public function generate(CodeContext $codeContext): string
    {
        $reflection = $this->classReflector->reflect(
            $this->classUtil->getClassNameFromSource($codeContext->getSource())
        );

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($propertyVisitor = $this->assignedPropertiesVisitor);
        $nodeTraverser->addVisitor($variableVisitor = new VariableCollectionVisitor(new CompilerContext($this->classReflector, $reflection)));
        $nodeTraverser->traverse([$reflection->getAst()]);

        $missingProperties = $this->resolveMissingProperties($propertyVisitor, $reflection);

        $snippet = [];

        foreach ($missingProperties as $missingProperty) {
            $type = $this->resolveType($variableVisitor, $missingProperty);
            $snippet[] = '/**';
            $snippet[] = ' * @var ' . ($type ?: 'mixed');
            $snippet[] = ' */';
            $snippet[] = sprintf(
                'private $%s;' . PHP_EOL,
                $missingProperty->var->name
            );
        }

        return implode(PHP_EOL, $snippet);
    }

    public function resolveMissingProperties(AssignedPropertiesVisitor $visitor, ReflectionClass $reflection)
    {

        $assigned = $visitor->getAssignedPropertyNodes();
        $properties = $reflection->getProperties();

        return array_filter($assigned, function (Node $property) use ($properties) {
            return !array_key_exists($property->var->name, $properties);
        });
    }

    private function resolveType(VariableCollectionVisitor $visitor, Node $propertyAssign)
    {
        $variables = $visitor->getVariables();
        $assignedVar = $propertyAssign->expr;

        if ($assignedVar instanceof String_ || $assignedVar instanceof EncapsedStringPart) {
            return 'string';
        } elseif ($assignedVar instanceof LNumber) {
            return 'int';
        } elseif ($assignedVar instanceof DNumber) {
            return 'float';
        } elseif ($assignedVar instanceof New_) {
            return (string) $assignedVar->class;
        }

        // TODO: Take into account variable scope -- this just does a name
        //       match on all variables in any method and takes the first one.
        foreach ($variables as $variable) {
            if ($variable->getName() != $assignedVar->name) {
                continue;
            }

            return (string) $variable->getType();
        }
    }
}
