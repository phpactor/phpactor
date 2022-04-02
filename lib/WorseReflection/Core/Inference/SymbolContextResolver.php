<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\CloneExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
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
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\ClassLike;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Psr\Log\LoggerInterface;

class SymbolContextResolver
{
    private Reflector $reflector;
    
    private LoggerInterface $logger;
    
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
        array $resolverMap = []
    ) {
        $this->logger = $logger;
        $this->reflector = $reflector;
        $this->cache = $cache;
        $this->resolverMap = $resolverMap;
    }

    /**
     * @param Node|Token $node
     */
    public function resolveNode(Frame $frame, $node): NodeContext
    {
        try {
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

    private function classTypeFromNode(Node $node): Type
    {
        return NodeUtil::nodeContainerClassLikeType($this->reflector, $node);
    }
}
