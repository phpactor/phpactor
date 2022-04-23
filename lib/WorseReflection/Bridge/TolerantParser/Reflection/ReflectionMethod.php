<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\TokenKind;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod as CoreReflectionMethod;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection as CoreReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\TypeResolver\MethodTypeResolver;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver\DeclaredMemberTypeResolver;
use Microsoft\PhpParser\NamespacedNameInterface;
use InvalidArgumentException;

class ReflectionMethod extends AbstractReflectionClassMember implements CoreReflectionMethod
{
    private ServiceLocator $serviceLocator;
    
    private MethodDeclaration $node;
    
    private ReflectionClassLike $class;
    
    private MethodTypeResolver $returnTypeResolver;
    
    private DeclaredMemberTypeResolver $memberTypeResolver;

    public function __construct(
        ServiceLocator $serviceLocator,
        ReflectionClassLike $class,
        MethodDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->class = $class;
        $this->returnTypeResolver = new MethodTypeResolver($this);
        $this->memberTypeResolver = new DeclaredMemberTypeResolver($this->serviceLocator->reflector());
    }

    public function name(): string
    {
        return $this->node->getName();
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

        return $this->serviceLocator->reflector()->reflectClassLike(ClassName::fromString($class));
    }

    public function parameters(): CoreReflectionParameterCollection
    {
        return ReflectionParameterCollection::fromMethodDeclaration($this->serviceLocator, $this->node, $this);
    }

    public function inferredType(): Type
    {
        $type = $this->returnTypeResolver->resolve();

        if (!$this->node->returnTypeList) {
            return $type;
        }

        if (($type->isDefined())) {
            return $type;
        }

        return $this->memberTypeResolver->resolveTypes(
            $this->node,
            $this->node->returnTypeList,
            $this->class()->name(), // note: this call is quite expensive
            $this->node->questionToken ? true : false
        );
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
        return NodeText::fromString(implode(PHP_EOL, array_reduce($statements, function ($acc, $statement) {
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

    protected function node(): Node
    {
        return $this->node;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }
}
