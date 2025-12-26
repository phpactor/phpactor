<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\Helper\NodeQuery;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\TextDocument\NodeToTextDocumentConverter;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Reflector;

class WorseNamedParameterCompletor implements TolerantCompletor
{
    public function __construct(
        private readonly Reflector $reflector,
        private readonly ObjectFormatter $formatter
    ) {
    }


    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $subject = NodeQuery::firstAncestorOrSelfInVia(
            $node,
            [
                MemberAccessExpression::class,
                ObjectCreationExpression::class,
                Attribute::class,
                CallExpression::class,
            ],
            [
                ArgumentExpression::class,
                ArgumentExpressionList::class
            ]
        );

        if (null === $subject) {
            return true;
        }

        if ($node instanceof Variable || $node instanceof StringLiteral) {
            return true;
        }

        if ($subject instanceof ObjectCreationExpression) {
            return yield from $this->fromObjectCreation($subject);
        }

        if ($subject instanceof Attribute) {
            return yield from $this->fromAttribute($subject);
        }

        if ($subject instanceof CallExpression) {
            return yield from $this->fromCallExpression($subject);
        }

        return true;
    }

    /**
     * @return Generator<Suggestion>
     */
    private function fromObjectCreation(ObjectCreationExpression $creation): Generator
    {
        $type = $creation->classTypeDesignator;

        if (!$type instanceof QualifiedName) {
            return true;
        }

        try {
            $class = $this->reflector->reflectClass((string)$type->getResolvedName());
        } catch (NotFound) {
            return true;
        }

        yield from $this->fromMethod($class, '__construct');

        return true;
    }

    /**
     * @return Generator<Suggestion>
     */
    private function fromAttribute(Attribute $attribute): Generator
    {
        /** @var QualifiedName|null $type */
        $type = $attribute->name;

        if (!$type instanceof QualifiedName) {
            return true;
        }

        try {
            $class = $this->reflector->reflectClass((string)$type->getResolvedName());
        } catch (NotFound) {
            return true;
        }

        yield from $this->fromMethod($class, '__construct');

        return true;
    }

    /**
     * @return Generator<Suggestion>
     */
    private function fromMethod(ReflectionClassLike $class, string $method): Generator
    {
        if (!$class->methods()->has($method)) {
            return true;
        }

        foreach ($class->methods()->get($method)->parameters() as $parameter) {
            yield Suggestion::createWithOptions(
                sprintf('%s: ', $parameter->name()),
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'priority' => Suggestion::PRIORITY_HIGH,
                    'short_description' => $this->formatter->format($parameter),
                ]
            );
        }
    }

    /**
     * @return Generator<Suggestion>
     */
    private function fromFunction(ReflectionFunction $function): Generator
    {
        foreach ($function->parameters() as $parameter) {
            yield Suggestion::createWithOptions(
                sprintf('%s: ', $parameter->name()),
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'priority' => Suggestion::PRIORITY_HIGH,
                    'short_description' => $this->formatter->format($parameter),
                ]
            );
        }
    }

    /**
     * @return Generator<Suggestion>
     */
    private function fromCallExpression(CallExpression $creation): Generator
    {
        /** @var Node */
        $callableExpression = $creation->callableExpression;
        if (
            !$callableExpression instanceof MemberAccessExpression &&
            !$callableExpression instanceof QualifiedName &&
            !$callableExpression instanceof ScopedPropertyAccessExpression
        ) {
            return true;
        }

        if ($callableExpression instanceof QualifiedName) {
            try {
                $function = $this->reflector->reflectFunction(
                    $callableExpression->getNamespacedName()->__toString()
                );
                yield from $this->fromFunction($function);
            } catch (NotFound) {
                return true;
            }

            return true;
        }

        try {
            $classLike = $this->reflector->reflectMethodCall(
                NodeToTextDocumentConverter::convert($creation),
                $callableExpression->getEndPosition()
            );
            yield from $this->fromMethod($classLike->class(), $classLike->name());
        } catch (NotFound) {
            return true;
        }

        return true;
    }
}
