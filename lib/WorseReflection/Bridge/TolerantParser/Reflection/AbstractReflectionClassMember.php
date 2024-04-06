<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\AttributeGroup;
use Microsoft\PhpParser\Token;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\ClassLike;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Util\OriginalMethodResolver;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use InvalidArgumentException;

abstract class AbstractReflectionClassMember extends AbstractReflectedNode implements ReflectionMember
{
    public function declaringClass(): ReflectionClassLike
    {
        $classDeclaration = $this->node()->getFirstAncestor(ClassLike::class);

        assert($classDeclaration instanceof NamespacedNameInterface);

        $class = $classDeclaration->getNamespacedName();

        if (null === $class) {
            throw new InvalidArgumentException(sprintf(
                'Could not locate class-like ancestor node for member "%s"',
                $this->name()
            ));
        }

        return $this->serviceLocator()->reflector()->reflectClassLike(ClassName::fromString($class));
    }

    public function original(): ReflectionMember
    {
        return (new OriginalMethodResolver())->resolveOriginalMember($this);
    }

    public function frame(): Frame
    {
        return $this->serviceLocator()->frameBuilder()->build($this->node());
    }

    public function docblock(): DocBlock
    {
        return $this->serviceLocator()->docblockFactory()->create(
            $this->node()->getLeadingCommentAndWhitespaceText(),
            $this->scope()
        );
    }

    public function visibility(): Visibility
    {
        $node = $this->node();
        if (!$node instanceof PropertyDeclaration && !$node instanceof ClassConstDeclaration && !$node instanceof MethodDeclaration) {
            return Visibility::public();
        }
        foreach ($node->modifiers as $token) {
            if ($token->kind === TokenKind::PrivateKeyword) {
                return Visibility::private();
            }

            if ($token->kind === TokenKind::ProtectedKeyword) {
                return Visibility::protected();
            }
        }

        return Visibility::public();
    }

    public function deprecation(): Deprecation
    {
        return $this->docblock()->deprecation();
    }

    public function position(): ByteOffsetRange
    {
        if (null === $this->node()->getFirstChildNode(AttributeGroup::class)) {
            return parent::position();
        }

        $tokenKind = match ($this->memberType()) {
            ReflectionMember::TYPE_PROPERTY => TokenKind::VariableName,
            ReflectionMember::TYPE_METHOD => TokenKind::FunctionKeyword,
            ReflectionMember::TYPE_CONSTANT => TokenKind::ConstKeyword,
            ReflectionMember::TYPE_CASE => TokenKind::CaseKeyword,
        };

        $name = $this->findDescendantNamedToken($tokenKind);

        if (null === $name) {
            return parent::position();
        }

        return ByteOffsetRange::fromInts(
            $name->getStartPosition(),
            $this->node()->getEndPosition()
        );
    }

    abstract protected function serviceLocator(): ServiceLocator;

    private function findDescendantNamedToken(int $tokenBeforeKind): ?Token
    {
        $found = false;

        foreach ($this->node()->getDescendantTokens() as $token) {
            if (false === $found) {
                if (!$token instanceof Token || $token->kind !== $tokenBeforeKind) {
                    continue;
                }

                if ($tokenBeforeKind === TokenKind::VariableName) {
                    return $token;
                }

                $found = true;
                continue;
            }

            if ($tokenBeforeKind !== TokenKind::ConstKeyword) {
                return $token->kind === TokenKind::Name ? $token : null;
            }

            if ($token->kind === TokenKind::Name && $token->getText($this->node()->getFileContents()) === $this->name()) {
                return $token;
            }
        }

        return null;
    }
}
