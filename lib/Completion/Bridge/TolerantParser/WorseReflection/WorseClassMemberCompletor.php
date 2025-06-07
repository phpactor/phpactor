<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Token;
use Phpactor\Completion\Bridge\ObjectRenderer\ItemDocumentation;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\ClassMemberQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\ObjectRenderer\Model\ObjectRenderer;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassLikeType;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use function in_array;
use function str_starts_with;
use function strlen;
use function substr;

class WorseClassMemberCompletor implements TolerantCompletor, TolerantQualifiable
{
    public function __construct(
        private Reflector $reflector,
        private ObjectFormatter $formatter,
        private ObjectFormatter $snippetFormatter,
        private ObjectRenderer $objectRenderer
    ) {
    }

    public function qualifier(): TolerantQualifier
    {
        return new ClassMemberQualifier();
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $memberStartOffset = $offset;
        $isInstance = true;

        if ($node instanceof MemberAccessExpression) {
            $memberStartOffset = $node->arrowToken->getFullStartPosition();
        }

        if ($node instanceof ScopedPropertyAccessExpression) {
            $memberStartOffset = $node->doubleColon->getFullStartPosition();
            $isInstance = false;
        }

        assert($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression);

        $memberName = $node->memberName;

        if ($memberName instanceof Variable) {
            $memberName = $memberName->name;
        }

        if (!$memberName instanceof Token) {
            return true;
        }

        $shouldCompleteOnlyName = strlen($source) > $offset->toInt() && substr($source, $offset->toInt(), 1) == '(';

        $partialMatch = (string) $memberName->getText($node->getFileContents());

        $reflectionOffset = $this->reflector->reflectOffset($source, $memberStartOffset);

        $nodeContext = $reflectionOffset->nodeContext();
        $type = $nodeContext->type();
        $static = $node instanceof ScopedPropertyAccessExpression;

        foreach ($type->expandTypes()->classLike() as $type) {
            foreach ($this->populateSuggestions($nodeContext, $type, $static, $shouldCompleteOnlyName, $isInstance) as $suggestion) {
                if ($partialMatch && !str_starts_with($suggestion->name(), $partialMatch)) {
                    continue;
                }

                yield $suggestion;
            }
        }

        return true;
    }

    /**
     * @return Generator<Suggestion>
     */
    private function populateSuggestions(NodeContext $nodeContext, Type $type, bool $static, bool $completeOnlyName, bool $isInstance): Generator
    {
        if (false === ($type->isDefined())) {
            return;
        }

        $isParent = $nodeContext->symbol()->name() === 'parent';
        $publicOnly = !in_array($nodeContext->symbol()->name(), ['this', 'self'], true);


        $type = $type->expandTypes()->classLike()->firstOrNull();

        if (!$type) {
            return;
        }

        if (!$type instanceof ClassLikeType) {
            return;
        }

        $members = $type->members();

        if (!$isParent && $static) {
            yield Suggestion::createWithOptions('class', [
                'type' => Suggestion::TYPE_CONSTANT,
                'short_description' => $type->name(),
                'priority' => Suggestion::PRIORITY_HIGH,
            ]);
        }

        try {
            $classReflection = $this->reflector->reflectClassLike($type->name());
        } catch (NotFound) {
            return;
        }

        foreach ($members->methods() as $method) {
            if (false === $isParent && $method->name() === '__construct') {
                continue;
            }
            if ($publicOnly && false === $method->visibility()->isPublic()) {
                continue;
            }

            if (!$isParent && $static && false === $method->isStatic()) {
                continue;
            }

            $snippet = [];
            if ($this->snippetFormatter->canFormat($method)) {
                $snippet['snippet'] = $completeOnlyName ? $method->name() : $this->snippetFormatter->format($method);
            }

            yield Suggestion::createWithOptions($method->name(), [
                'type' => Suggestion::TYPE_METHOD,
                'short_description' => fn () => $this->formatter->format($method),
                'documentation' => function () use ($method) {
                    return $this->objectRenderer->render(new ItemDocumentation(sprintf(
                        '%s::%s',
                        $method->class()->name(),
                        $method->name()
                    ), $method->docblock()->formatted(), $method));
                },
                ...$snippet
            ]);
        }

        if ($classReflection instanceof ReflectionClass) {
            /** @var ReflectionProperty $property */
            foreach ($members->properties() as $property) {
                if ($publicOnly && false === $property->visibility()->isPublic()) {
                    continue;
                }

                if ($static && false === $property->isStatic()) {
                    continue;
                }

                $name = $property->name();
                if ($static) {
                    $name = '$' . $name;
                }

                yield Suggestion::createWithOptions($name, [
                    'type' => Suggestion::TYPE_PROPERTY,
                    'short_description' => fn () => $this->formatter->format($property),
                    'documentation' => function () use ($property) {
                        return $this->objectRenderer->render(new ItemDocumentation(sprintf(
                            '%s::%s',
                            $property->class()->name(),
                            $property->name()
                        ), $property->docblock()->formatted(), $property));
                    },
                ]);
            }
        }

        if (false === $isInstance && $classReflection instanceof ReflectionClass ||
            $classReflection instanceof ReflectionInterface ||
            $classReflection instanceof ReflectionEnum
        ) {
            foreach ($members->constants() as $constant) {
                if ($publicOnly && false === $constant->visibility()->isPublic()) {
                    continue;
                }

                yield Suggestion::createWithOptions($constant->name(), [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'short_description' => fn () => $this->formatter->format($constant),
                    'documentation' => fn () => $constant->docblock()->formatted(),
                ]);
            }
        }

        if ($classReflection instanceof ReflectionEnum) {
            foreach ($members->enumCases() as $case) {
                yield Suggestion::createWithOptions($case->name(), [
                    'type' => Suggestion::TYPE_ENUM,
                    'short_description' => fn () => $this->formatter->format($case),
                    'documentation' => fn () => $case->docblock()->formatted(),
                ]);
            }
        }
    }
}
