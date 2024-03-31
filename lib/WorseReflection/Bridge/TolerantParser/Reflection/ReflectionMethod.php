<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\AttributeGroup;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\MemberTypeContextualiser;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod as CoreReflectionMethod;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection as CoreReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\TypeResolver\MethodTypeResolver;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver\DeclaredMemberTypeResolver;
use Microsoft\PhpParser\NamespacedNameInterface;
use InvalidArgumentException;

class ReflectionMethod extends AbstractReflectionClassMember implements CoreReflectionMethod
{
    private MethodTypeResolver $returnTypeResolver;

    private DeclaredMemberTypeResolver $memberTypeResolver;

    private ?string $name = null;

    private MemberTypeContextualiser $typeContextualiser;

    public function __construct(
        private ServiceLocator $serviceLocator,
        private ReflectionClassLike $class,
        private MethodDeclaration $node
    ) {
        $this->returnTypeResolver = new MethodTypeResolver($this);
        $this->memberTypeResolver = new DeclaredMemberTypeResolver($this->serviceLocator->reflector());
        $this->typeContextualiser = new MemberTypeContextualiser();
    }

    public function name(): string
    {
        if ($this->name) {
            return $this->name;
        }
        $this->name = (string)$this->node->getName();
        return $this->name;
    }

    public function nameRange(): ByteOffsetRange
    {
        $name = $this->node->name;
        return ByteOffsetRange::fromInts($name->getStartPosition(), $name->getEndPosition());
    }

    public function declaringClass(): ReflectionClassLike
    {
        $classDeclaration = $this->node->getFirstAncestor(ClassLike::class);

        assert($classDeclaration instanceof NamespacedNameInterface);
        $class = $classDeclaration->getNamespacedName();


        /** @phpstan-ignore-next-line */
        if (null === $class) {
            throw new InvalidArgumentException(sprintf(
                'Could not locate class-like ancestor node for method "%s"',
                $this->name()
            ));
        }


        $className = ClassName::fromString($class);
        if ($className == $this->class()->name()) {
            return $this->class();
        }
        return $this->serviceLocator->reflector()->reflectClassLike($className);
    }

    public function parameters(): CoreReflectionParameterCollection
    {
        return ReflectionParameterCollection::fromMethodDeclaration($this->serviceLocator, $this->node, $this);
    }

    public function inferredType(): Type
    {
        $type = $this->typeContextualiser->contextualise(
            $this->declaringClass(),
            $this->class(),
            $this->returnTypeResolver->resolve($this->class())
        );

        if (($type->isDefined())) {
            return $type;
        }

        return $this->type();
    }

    /**
     * @deprecated use type()
     */
    public function returnType(): Type
    {
        return $this->type();
    }

    public function type(): Type
    {
        return $this->memberTypeResolver->resolve(
            $this->node,
            $this->node->returnTypeList,
            $this->class()->name(),
            $this->node->questionToken ? true : false
        );
    }

    public function body(): NodeText
    {
        $statement = $this->node->compoundStatementOrSemicolon;
        if (!$statement instanceof CompoundStatementNode) {
            return NodeText::fromString('');
        }
        $statements = $statement->statements;
        return NodeText::fromString(implode("\n", array_reduce($statements, function ($acc, $statement) {
            $acc[] = (string) $statement->getText();
            return $acc;
        }, [])));
    }

    public function class(): ReflectionClassLike
    {
        return $this->class;
    }

    public function isStatic(): bool
    {
        return $this->node->isStatic();
    }

    public function isAbstract(): bool
    {
        foreach ($this->node->modifiers as $token) {
            if ($token->kind === TokenKind::AbstractKeyword) {
                return true;
            }
        }

        return false;
    }

    public function isVirtual(): bool
    {
        return false;
    }

    public function memberType(): string
    {
        return ReflectionMember::TYPE_METHOD;
    }

    public function withClass(ReflectionClassLike $class): ReflectionMember
    {
        return new self($this->serviceLocator, $class, $this->node);
    }

    public function position(): ByteOffsetRange
    {
        if (null === $this->node()->getFirstChildNode(AttributeGroup::class)) {
            return parent::position();
        }

        $name = $this->findDescendantToken();

        if (null === $name) {
            return parent::position();
        }

        return ByteOffsetRange::fromInts(
            $name->getStartPosition(),
            $this->node()->getEndPosition()
        );
    }

    protected function node(): Node
    {
        return $this->node;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }

    private function findDescendantToken(int $tokenBeforeKind = TokenKind::FunctionKeyword): ?Token
    {
        $found = false;

        foreach ($this->node()->getDescendantNodesAndTokens() as $nodeOrToken) {
            if (!$found && (!$nodeOrToken instanceof Token || $nodeOrToken->kind !== $tokenBeforeKind)) {
                continue;
            }

            assert($nodeOrToken instanceof Token);

            if ($found and $nodeOrToken->kind === TokenKind::Name) {
                return $nodeOrToken;
            }

            $found = true;
            continue;
        }

        return null;
    }
}
