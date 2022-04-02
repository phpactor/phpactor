<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\CloneExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\Node\Expression\Variable as ParserVariable;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use Psr\Log\LoggerInterface;

class SymbolContextResolver
{
    private MemberTypeResolver $memberTypeResolver;
    
    private Reflector $reflector;
    
    private LoggerInterface $logger;
    
    private FullyQualifiedNameResolver $nameResolver;
    
    private ExpressionEvaluator $expressionEvaluator;
    
    private Cache $cache;

    /**
     * @var array<class-name,Resolver>
     */
    private array $resolverMap;
    
    /**
     * @param array<class-name,Resolver> $resolverMap
     */
    public function __construct(
        Reflector $reflector,
        LoggerInterface $logger,
        Cache $cache,
        FullyQualifiedNameResolver $nameResolver,
        array $resolverMap = [],
    ) {
        $this->logger = $logger;
        $this->memberTypeResolver = new MemberTypeResolver($reflector);
        $this->nameResolver = $nameResolver;
        $this->reflector = $reflector;
        $this->expressionEvaluator = new ExpressionEvaluator();
        $this->cache = $cache;
        $this->resolverMap = $resolverMap;
    }

    /**
     * @param Node|Token $node
     */
    public function resolveNode(Frame $frame, $node): NodeContext
    {
        try {
            if (
                $node instanceof ParserVariable
                && $node->parent instanceof ScopedPropertyAccessExpression
                && $node === $node->parent->memberName
            ) {
                return $this->doResolveNodeWithCache($frame, $node->parent);
            }
            return $this->doResolveNodeWithCache($frame, $node);
        } catch (CouldNotResolveNode $couldNotResolveNode) {
            return NodeContext::none()
                ->withIssue($couldNotResolveNode->getMessage());
        }
    }

    public function reflector(): Reflector
    {
        return $this->reflector;
    }

    /**
     * @param Node|Token $node
     */
    private function doResolveNodeWithCache(Frame $frame, $node): NodeContext
    {
        $key = 'sc:'.spl_object_hash($node);

        return $this->cache->getOrSet($key, function () use ($frame, $node) {
            if (false === $node instanceof Node) {
                throw new CouldNotResolveNode(sprintf(
                    'Non-node class passed to resolveNode, got "%s"',
                    get_class($node)
                ));
            }

            $context = $this->doResolveNode($frame, $node);
            $context = $context->withScope(new ReflectionScope($this->reflector, $node));

            return $context;
        });
    }

    private function doResolveNode(Frame $frame, Node $node): NodeContext
    {
        $this->logger->debug(sprintf('Resolving: %s', get_class($node)));

        if (isset($this->resolverMap[get_class($node)])) {
            return $this->resolverMap[get_class($node)]->resolve($this, $frame, $node);
        }

        if ($node instanceof ParserVariable) {
            return $this->resolveVariable($frame, $node);
        }

        if ($node instanceof MemberAccessExpression) {
            return $this->resolveMemberAccessExpression($frame, $node);
        }

        if ($node instanceof CallExpression) {
            return $this->resolveCallExpression($frame, $node);
        }

        if ($node instanceof ScopedPropertyAccessExpression) {
            return $this->resolveScopedPropertyAccessExpression($frame, $node);
        }

        if ($node instanceof ParenthesizedExpression) {
            return $this->resolveParenthesizedExpression($frame, $node);
        }

        if ($node instanceof BinaryExpression) {
            $value = $this->expressionEvaluator->evaluate($node);
            return NodeContextFactory::create(
                $node->getText(),
                $node->getStartPosition(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::CLASS_,
                    'type' => TypeFactory::fromValue($value),
                    'value' => $value,
                ]
            );
        }

        if ($node instanceof ClassDeclaration || $node instanceof TraitDeclaration || $node instanceof InterfaceDeclaration) {
            return NodeContextFactory::create(
                (string)$node->name->getText((string)$node->getFileContents()),
                $node->name->getStartPosition(),
                $node->name->getEndPosition(),
                [
                    'symbol_type' => Symbol::CLASS_,
                    'type' => TypeFactory::fromStringWithReflector($node->getNamespacedName(), $this->reflector)
                ]
            );
        }

        if ($node instanceof FunctionDeclaration) {
            return NodeContextFactory::create(
                (string)$node->name->getText((string)$node->getFileContents()),
                $node->name->getStartPosition(),
                $node->name->getEndPosition(),
                [
                    'symbol_type' => Symbol::FUNCTION,
                ]
            );
        }

        if ($node instanceof ObjectCreationExpression) {
            return $this->resolveObjectCreationExpression($frame, $node);
        }

        if ($node instanceof SubscriptExpression) {
            $variableValue = $this->doResolveNodeWithCache($frame, $node->postfixExpression);
            return $this->resolveSubscriptExpression($frame, $variableValue, $node);
        }

        if ($node instanceof StringLiteral) {
            return NodeContextFactory::create(
                (string) $node->getStringContentsText(),
                $node->getStartPosition(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::STRING,
                    'type' => TypeFactory::string(),
                    'value' => (string) $node->getStringContentsText(),
                    'container_type' => $this->classTypeFromNode($node)
                ]
            );
        }

        if ($node instanceof NumericLiteral) {
            return $this->resolveNumericLiteral($node);
        }

        if ($node instanceof ReservedWord) {
            return $this->resolveReservedWord($node);
        }

        if ($node instanceof ArrayCreationExpression) {
            return $this->resolveArrayCreationExpression($frame, $node);
        }

        if ($node instanceof ArgumentExpression) {
            return $this->doResolveNodeWithCache($frame, $node->expression);
        }

        if ($node instanceof TernaryExpression) {
            return $this->resolveTernaryExpression($frame, $node);
        }

        if ($node instanceof MethodDeclaration) {
            return $this->resolveMethodDeclaration($frame, $node);
        }

        if ($node instanceof CloneExpression) {
            return $this->resolveCloneExpression($frame, $node);
        }

        throw new CouldNotResolveNode(sprintf(
            'Did not know how to resolve node of type "%s" with text "%s"',
            get_class($node),
            $node->getText()
        ));
    }

    private function resolveVariable(Frame $frame, ParserVariable $node): NodeContext
    {
        if ($node->getFirstAncestor(PropertyDeclaration::class)) {
            return $this->resolvePropertyVariable($node);
        }

        $name = $node->getText();
        return $this->resolveVariableName($name, $node, $frame);
    }

    private function resolvePropertyVariable(ParserVariable $node): NodeContext
    {
        $info = NodeContextFactory::create(
            $node->getName(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::PROPERTY,
            ]
        );

        return $this->memberTypeResolver->propertyType(
            $this->classTypeFromNode($node),
            $info,
            $info->symbol()->name()
        );
    }

    private function resolveMemberAccessExpression(Frame $frame, MemberAccessExpression $node): NodeContext
    {
        $class = $this->doResolveNodeWithCache($frame, $node->dereferencableExpression);

        return $this->_infoFromMemberAccess($frame, $class->type(), $node);
    }

    private function resolveCallExpression(Frame $frame, CallExpression $node): NodeContext
    {
        $resolvableNode = $node->callableExpression;
        return $this->doResolveNodeWithCache($frame, $resolvableNode);
    }

    private function resolveNumericLiteral(NumericLiteral $node): NodeContext
    {
        // Strip PHP 7.4 underscorse separator before comparison
        $value = $this->convertNumericStringToInternalType(
            str_replace('_', '', $node->getText())
        );

        return NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::NUMBER,
                'type' => is_float($value) ? TypeFactory::float() : TypeFactory::int(),
                'value' => $value,
                'container_type' => $this->classTypeFromNode($node)
            ]
        );
    }

    /**
     * @return int|float
     */
    private function convertNumericStringToInternalType(string $value)
    {
        if (1 === preg_match('/^[1-9][0-9]*$/', $value)) {
            return (int) $value;
        }
        if (1 === preg_match('/^0[xX][0-9a-fA-F]+$/', $value)) {
            return hexdec(substr($value, 2));
        }
        if (1 === preg_match('/^0[0-7]+$/', $value)) {
            return octdec(substr($value, 1));
        }
        if (1 === preg_match('/^0[bB][01]+$/', $value)) {
            return bindec(substr($value, 2));
        }

        return (float) $value;
    }

    private function resolveReservedWord(Node $node): NodeContext
    {
        $symbolType = $containerType = $type = $value = null;
        $word = strtolower($node->getText());

        if ('null' === $word) {
            $type = TypeFactory::null();
            $symbolType = Symbol::BOOLEAN;
            $containerType = $this->classTypeFromNode($node);
        }

        if ('false' === $word) {
            $value = false;
            $type = TypeFactory::bool();
            $symbolType = Symbol::BOOLEAN;
            $containerType = $this->classTypeFromNode($node);
        }

        if ('true' === $word) {
            $type = TypeFactory::bool();
            $value = true;
            $symbolType = Symbol::BOOLEAN;
            $containerType = $this->classTypeFromNode($node);
        }

        $info = NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'value' => $value,
                'type' => $type,
                'symbol_type' => $symbolType === null ? Symbol::UNKNOWN : $symbolType,
                'container_type' => $containerType,
            ]
        );

        if (null === $symbolType) {
            $info = $info->withIssue(sprintf('Could not resolve reserved word "%s"', $node->getText()));
        }

        if (null === $type) {
            $info = $info->withIssue(sprintf('Could not resolve reserved word "%s"', $node->getText()));
        }

        return $info;
    }

    private function resolveArrayCreationExpression(Frame $frame, ArrayCreationExpression $node): NodeContext
    {
        $array  = [];

        if (null === $node->arrayElements) {
            return NodeContextFactory::create(
                $node->getText(),
                $node->getStartPosition(),
                $node->getEndPosition(),
                [
                    'type' => TypeFactory::array(),
                    'value' => []
                ]
            );
        }

        foreach ($node->arrayElements->getElements() as $element) {
            $value = $this->doResolveNodeWithCache($frame, $element->elementValue)->value();
            if ($element->elementKey) {
                $key = $this->doResolveNodeWithCache($frame, $element->elementKey)->value();
                $array[$key] = $value;
                continue;
            }

            $array[] = $value;
        }

        return NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'type' => TypeFactory::array(),
                'value' => $array
            ]
        );
    }

    private function resolveSubscriptExpression(
        Frame $frame,
        NodeContext $info,
        SubscriptExpression $node = null
    ): NodeContext {
        if (null === $node->accessExpression) {
            $info = $info->withIssue(sprintf(
                'Subscript expression "%s" is incomplete',
                (string) $node->getText()
            ));
            return $info;
        }

        $node = $node->accessExpression;
        $type = $info->type();

        if (!$type instanceof ArrayType) {
            $info = $info->withIssue(sprintf(
                'Not resolving subscript expression of type "%s"',
                (string) $info->type()
            ));
            return $info;
        }

        $info = $info->withType($type->valueType);
        $subjectValue = $info->value();

        if (false === is_array($subjectValue)) {
            $info = $info->withIssue(sprintf(
                'Array value for symbol "%s" is not an array, is a "%s"',
                (string) $info->symbol(),
                gettype($subjectValue)
            ));

            return $info;
        }

        if ($node instanceof StringLiteral) {
            $string = $this->doResolveNodeWithCache($frame, $node);

            if (array_key_exists($string->value(), $subjectValue)) {
                $value = $subjectValue[$string->value()];
                return $string->withValue($value);
            }
        }

        $info = $info->withIssue(sprintf(
            'Did not resolve access expression for node type "%s"',
            get_class($node)
        ));

        return $info;
    }

    private function resolveScopedPropertyAccessExpression(Frame $frame, ScopedPropertyAccessExpression $node): NodeContext
    {
        $name = null;
        if ($node->scopeResolutionQualifier instanceof ParserVariable) {
            $context = $this->resolveVariable($frame, $node->scopeResolutionQualifier);
            $type = $context->type();
            if ($type instanceof ClassType) {
                $name = $type->name->__toString();
            }
        }

        if (empty($name)) {
            $name = $node->scopeResolutionQualifier->getText();
        }

        $parent = $this->nameResolver->resolve($node, $name);

        return $this->_infoFromMemberAccess($frame, $parent, $node);
    }

    private function resolveObjectCreationExpression(Frame $frame, ObjectCreationExpression $node): NodeContext
    {
        if (false === $node->classTypeDesignator instanceof Node) {
            throw new CouldNotResolveNode(sprintf('Could not create object from "%s"', get_class($node)));
        }

        return $this->doResolveNodeWithCache($frame, $node->classTypeDesignator);
    }

    private function resolveTernaryExpression(Frame $frame, TernaryExpression $node): NodeContext
    {
        // @phpstan-ignore-next-line
        if ($node->ifExpression) {
            $ifValue = $this->doResolveNodeWithCache($frame, $node->ifExpression);

            if (!$ifValue->type() instanceof MissingType) {
                return $ifValue;
            }
        }

        // if expression was not defined, fallback to condition
        $conditionValue = $this->doResolveNodeWithCache($frame, $node->condition);

        if (!$conditionValue->type() instanceof MissingType) {
            return $conditionValue;
        }

        return NodeContext::none();
    }

    private function resolveMethodDeclaration(Frame $frame, MethodDeclaration $node): NodeContext
    {
        $classNode = $this->getClassLikeAncestor($node);
        $classSymbolContext = $this->doResolveNodeWithCache($frame, $classNode);

        return NodeContextFactory::create(
            (string)$node->name->getText($node->getFileContents()),
            $node->name->getStartPosition(),
            $node->name->getEndPosition(),
            [
                'container_type' => $classSymbolContext->type(),
                'symbol_type' => Symbol::METHOD,
            ]
        );
    }

    private function _infoFromMemberAccess(Frame $frame, Type $classType, Node $node): NodeContext
    {
        assert($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression);

        $memberName = NodeUtil::nameFromTokenOrNode($node, $node->memberName);
        $memberType = $node->getParent() instanceof CallExpression ? Symbol::METHOD : Symbol::PROPERTY;

        if ($node->memberName instanceof Node) {
            $memberNameInfo = $this->doResolveNodeWithCache($frame, $node->memberName);
            if (is_string($memberNameInfo->value())) {
                $memberName = $memberNameInfo->value();
            }
        }

        if (
            Symbol::PROPERTY === $memberType
            && $node instanceof ScopedPropertyAccessExpression
            && is_string($memberName)
            && substr($memberName, 0, 1) !== '$'
        ) {
            $memberType = Symbol::CONSTANT;
        }

        $information = NodeContextFactory::create(
            (string)$memberName,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => $memberType,
            ]
        );

        // if the classType is a call expression, then this is a method call
        $info = $this->memberTypeResolver->{$memberType . 'Type'}($classType, $information, $memberName);

        if (Symbol::PROPERTY === $memberType) {
            $frameTypes = $this->getFrameTypesForPropertyAtPosition(
                $frame,
                (string) $memberName,
                $classType,
                $node->getEndPosition(),
            );

            foreach ($frameTypes as $types) {
                $info = $info->withTypes(
                    $info->types()->merge($types),
                );
            }
        }

        $this->logger->debug(sprintf(
            'Resolved type "%s" for %s "%s" of class "%s"',
            $info->type(),
            $memberType,
            $memberName,
            (string) $classType
        ));

        return $info;
    }

    private function resolveParenthesizedExpression(Frame $frame, ParenthesizedExpression $node): NodeContext
    {
        return $this->doResolveNode($frame, $node->expression);
    }

    private function resolveVariableName(string $name, Node $node, Frame $frame): NodeContext
    {
        $varName = ltrim($name, '$');
        $offset = $node->getEndPosition();
        $variables = $frame->locals()->lessThanOrEqualTo($offset)->byName($varName);

        if (0 === $variables->count()) {
            return NodeContextFactory::create(
                $name,
                $node->getStartPosition(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE
                ]
            )->withIssue(sprintf('Variable "%s" is undefined', $varName));
        }

        return $variables->last()->symbolContext();
    }

    /**
     * @return ClassDeclaration|TraitDeclaration|InterfaceDeclaration|null
     */
    private function getClassLikeAncestor(Node $node)
    {
        $ancestor = $node->getFirstAncestor(ObjectCreationExpression::class, ClassLike::class);

        if ($ancestor instanceof ObjectCreationExpression) {
            if ($ancestor->classTypeDesignator instanceof Token) {
                if ($ancestor->classTypeDesignator->kind == TokenKind::ClassKeyword) {
                    throw new CouldNotResolveNode('Resolving anonymous classes is not currently supported');
                }
            }

            return $this->getClassLikeAncestor($ancestor);
        }

        /** @var ClassDeclaration|TraitDeclaration|InterfaceDeclaration|null */
        return $ancestor;
    }

    private function resolveCloneExpression(Frame $frame, CloneExpression $node): NodeContext
    {
        return $this->doResolveNode($frame, $node->expression);
    }

    /**
     * @return Generator<int, Types, null, void>
     */
    private function getFrameTypesForPropertyAtPosition(
        Frame $frame,
        string $propertyName,
        Type $classType,
        int $position
    ): Generator {
        $assignments = $frame->properties()
            ->lessThanOrEqualTo($position)
            ->byName($propertyName)
        ;

        if (!$classType instanceof ClassType) {
            return;
        }

        /** @var Variable $variable */
        foreach ($assignments as $variable) {
            $symbolContext = $variable->symbolContext();
            $containerType = $symbolContext->containerType();

            if (!$containerType) {
                continue;
            }

            if (!$containerType instanceof ClassType) {
                continue;
            }

            if ($containerType->name != $classType->name) {
                continue;
            }

            yield $symbolContext->types();
        }
    }

    private function classTypeFromNode(Node $node): Type
    {
        return NodeUtil::nodeContainerClassLikeType($this->reflector, $node);
    }
}
