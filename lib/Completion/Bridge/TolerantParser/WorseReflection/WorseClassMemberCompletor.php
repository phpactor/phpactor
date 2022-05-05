<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Token;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\ClassMemberQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;

class WorseClassMemberCompletor implements TolerantCompletor, TolerantQualifiable
{
    private Reflector $reflector;

    private ObjectFormatter $formatter;

    private ObjectFormatter $snippetFormatter;

    public function __construct(
        Reflector $reflector,
        ObjectFormatter $formatter,
        ObjectFormatter $snippetFormatter
    ) {
        $this->reflector = $reflector;
        $this->formatter = $formatter;
        $this->snippetFormatter = $snippetFormatter;
    }

    public function qualifier(): TolerantQualifier
    {
        return new ClassMemberQualifier();
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $memberStartOffset = $offset;
        
        if ($node instanceof MemberAccessExpression) {
            $memberStartOffset = $node->arrowToken->getFullStartPosition();
        }

        if ($node instanceof ScopedPropertyAccessExpression) {
            $memberStartOffset = $node->doubleColon->getFullStartPosition();
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

        $symbolContext = $reflectionOffset->symbolContext();
        $type = $symbolContext->type();
        $static = $node instanceof ScopedPropertyAccessExpression;

        foreach ($type->toTypes() as $type) {
            foreach ($this->populateSuggestions($symbolContext, $type, $static, $shouldCompleteOnlyName) as $suggestion) {
                if ($partialMatch && 0 !== mb_strpos($suggestion->name(), $partialMatch)) {
                    continue;
                }

                yield $suggestion;
            }
        }

        return true;
    }

    private function populateSuggestions(NodeContext $symbolContext, Type $type, bool $static, bool $completeOnlyName): Generator
    {
        if (false === ($type->isDefined())) {
            return;
        }

        $type = $type->classNamedTypes()->firstOrNull();

        if (!$type) {
            return;
        }

        if ($static) {
            yield Suggestion::createWithOptions('class', [
                'type' => Suggestion::TYPE_CONSTANT,
                'short_description' => $type->name(),
                'priority' => Suggestion::PRIORITY_HIGH,
            ]);
        }

        try {
            $classReflection = $this->reflector->reflectClassLike($type->name());
        } catch (NotFound $notFound) {
            return;
        }

        $publicOnly = !in_array($symbolContext->symbol()->name(), ['this', 'self'], true);

        /** @var ReflectionMethod $method */
        foreach ($classReflection->methods() as $method) {
            if ($method->name() === '__construct') {
                continue;
            }
            if ($publicOnly && false === $method->visibility()->isPublic()) {
                continue;
            }

            if ($static && false === $method->isStatic()) {
                continue;
            }

            yield Suggestion::createWithOptions($method->name(), [
                'type' => Suggestion::TYPE_METHOD,
                'short_description' => $this->formatter->format($method),
                'documentation' => $method->docblock()->formatted(),
                'snippet' => $completeOnlyName ? $method->name() : $this->snippetFormatter->format($method),
            ]);
        }

        if ($classReflection instanceof ReflectionClass) {
            /** @var ReflectionProperty $property */
            foreach ($classReflection->properties() as $property) {
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
                    'short_description' => $this->formatter->format($property),
                    'documentation' => $property->docblock()->formatted()
                ]);
            }
        }

        if ($classReflection instanceof ReflectionClass ||
            $classReflection instanceof ReflectionInterface
        ) {
            foreach ($classReflection->constants() as $constant) {
                yield Suggestion::createWithOptions($constant->name(), [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'short_description' => $this->formatter->format($constant),
                    'documentation' => $constant->docblock()->formatted(),
                ]);
            }
        }

        if ($classReflection instanceof ReflectionEnum) {
            foreach ($classReflection->cases() as $case) {
                yield Suggestion::createWithOptions($case->name(), [
                    'type' => Suggestion::TYPE_ENUM,
                    'short_description' => $this->formatter->format($case),
                    'documentation' => $case->docblock()->formatted(),
                ]);
            }
        }
    }
}
