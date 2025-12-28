<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\AlwaysQualfifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\Completion\Core\CompletorDecorator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Context\ClassLikeContext;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassLikeType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class ContextSensitiveCompletor implements TolerantCompletor, TolerantQualifiable, CompletorDecorator
{
    public function __construct(
        private TolerantCompletor $inner,
        private Reflector $reflector
    ) {
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $generator = $this->inner->complete($node, $source, $offset);
        if (
            !$node instanceof CallExpression &&
            ($node instanceof QualifiedName && !$node->parent instanceof ObjectCreationExpression)
        ) {
            yield from $generator;
            return $generator->getReturn();
        }

        $type = $this->resolveFilterableType($node, $source, $offset);
        if (null === $type) {
            yield from $generator;
            return $generator->getReturn();
        }

        foreach ($generator as $suggestion) {
            $fqn = $suggestion->fqn();
            if (!$fqn) {
                yield $suggestion;
                continue;
            }

            try {
                $refection = $this->reflector->reflectClassLike($fqn);
            } catch (NotFound $e) {
                continue;
            }

            if (!$refection->isInstanceOf($type->name())) {
                continue;
            }
            yield $suggestion;
        }

        return $generator->getReturn();
    }

    public function qualifier(): TolerantQualifier
    {
        if ($this->inner instanceof TolerantQualifiable) {
            return $this->inner->qualifier();
        }

        return new AlwaysQualfifier();
    }

    public function decorates(): string
    {
        return $this->inner::class;
    }

    private function resolveFilterableType(Node $node, TextDocument $source, ByteOffset $offset): ?ClassLikeType
    {
        $argumentNb = 0;
        $memberAccessOrObjectCreation = $node;
        $node = NodeUtil::firstDescendantNodeBeforeOffset($node, $offset->toInt());

        if ($node instanceof QualifiedName) {
            $memberAccessOrObjectCreation = null;
            $argumentExpression = $node->parent?->parent;

            if ($argumentExpression instanceof ArgumentExpression) {
                $list = $argumentExpression->getFirstAncestor(ArgumentExpressionList::class);

                if (!$list instanceof ArgumentExpressionList) {
                    return null;
                }
                $argumentNb = NodeUtil::argumentOffset($list, $argumentExpression) ?? 0;
                $memberAccessOrObjectCreation = $list->parent;
            }
        } else {
            $argumentList = $node->parent?->parent;
            if ($argumentList instanceof ArgumentExpressionList) {
                $argumentNb = max(0, count(iterator_to_array($argumentList->getValues())) - 1);
                $memberAccessOrObjectCreation = $argumentList->parent;
            }
        }

        if (
            !$memberAccessOrObjectCreation instanceof CallExpression &&
            !$memberAccessOrObjectCreation instanceof ObjectCreationExpression
        ) {
            return null;
        }

        $offset = $memberAccessOrObjectCreation->openParen?->getStartPosition();
        if (null === $offset) {
            return null;
        }
        try {
            $memberAccessOrObjectCreation = $this->reflector->reflectOffset($source, $offset)->nodeContext();
        } catch (NotFound $e) {
            return null;
        }
        if ($memberAccessOrObjectCreation instanceof MemberAccessContext) {
            return $this->typeFromMemberAccess($memberAccessOrObjectCreation, $argumentNb);
        }

        if ($memberAccessOrObjectCreation instanceof ClassLikeContext) {
            return $this->typeFromClassInstantiation($memberAccessOrObjectCreation, $argumentNb);
        }

        return null;
    }
    /**
     * @param MemberAccessContext<ReflectionMember> $memberAccessOrObjectCreation
     */
    private function typeFromMemberAccess(MemberAccessContext $memberAccessOrObjectCreation, int $argumentNb): ?ClassLikeType
    {
        try {
            $functionLike = $memberAccessOrObjectCreation->accessedMember();
        } catch (NotFound) {
            return null;
        }
        if (!$functionLike instanceof ReflectionFunctionLike) {
            return null;
        }
        $parameters = $functionLike->parameters();
        return $this->typeFromParameters($parameters, $argumentNb);
    }

    /**
     * @param int<0, max> $argumentNb
     */
    private function typeFromClassInstantiation(ClassLikeContext $classLikeContext, int $argumentNb): ?ClassLikeType
    {
        try {
            $classLike = $classLikeContext->classLike();
            $constructor = $classLike->methods()->get('__construct');
            $parameters = $constructor->parameters();
        } catch (NotFound) {
            return null;
        }
        return $this->typeFromParameters($parameters, $argumentNb);
    }

    private function typeFromParameters(ReflectionParameterCollection $parameters, int $argumentNb): ?ClassLikeType
    {
        $parameter = $parameters->at($argumentNb);
        if (null === $parameter) {
            $lastParameter = $parameters->lastOrNull();
            if (null === $lastParameter) {
                return null;
            }
            if (!$lastParameter->isVariadic()) {
                return null;
            }
            $parameter = $lastParameter;
        }

        $type = $parameter->type();
        if ($parameter->isVariadic()) {
            if ($type instanceof ArrayType) {
                $type = $type->iterableValueType();
            }
        }
        if (!$type instanceof ClassLikeType) {
            return null;
        }

        return $type;
    }
}
