<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\AlwaysQualfifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassLikeType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class ContextSensitiveCompletor implements TolerantCompletor, TolerantQualifiable
{
    public function __construct(private TolerantCompletor $inner, private Reflector $reflector)
    {
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $generator = $this->inner->complete($node, $source, $offset);

        $argumentNb = 0;
        $callExpression = $node;
        if ($node instanceof QualifiedName) {
            $callExpression = null;
            $argumentExpression = $node->getFirstAncestor(ArgumentExpression::class);
            if ($argumentExpression instanceof ArgumentExpression) {
                $list = $argumentExpression->getFirstAncestor(ArgumentExpressionList::class);
                if (!$list instanceof ArgumentExpressionList) {
                    yield from $generator;
                    return $generator->getReturn();
                }
                $argumentNb = NodeUtil::argumentOffset($list, $argumentExpression) ?? 0;
                $callExpression = $list->parent;
            }
        }
        if ($node instanceof ArgumentExpressionList) {
            $argumentNb = count(iterator_to_array($node->getValues()));
            $callExpression = $node->parent;
        }

        if (!$callExpression instanceof CallExpression) {
            yield from $generator;
            return $generator->getReturn();
        }

        $callExpression = $this->reflector->reflectNode($source, $callExpression->openParen->getStartPosition());
        if (!$callExpression instanceof ReflectionMethodCall) {
            yield from $generator;
            return $generator->getReturn();
        }
        $functionLike = $callExpression->method();
        $parameters = $functionLike->parameters();
        $parameter = $parameters->at($argumentNb);
        if (null === $parameter) {
            yield from $generator;
            return $generator->getReturn();
        }

        $type = $parameter->type();
        if ($parameter->isVariadic()) {
            if ($type instanceof ArrayType) {
                $type = $type->iterableValueType();
            }
        }
        if (!$type instanceof ClassLikeType) {
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
                $refection = $this->reflector->reflectClass($fqn);
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
}
