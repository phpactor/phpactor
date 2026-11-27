<?php

declare(strict_types=1);

namespace Phpactor\CodeTransform\Adapter\TolerantParser\Refactor;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionObjectCreationExpression;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\CodeTransform\Domain\Refactor\ByteOffsetRefactor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Generator;
use Webmozart\Assert\Assert;

class TolerantNamedMethodParameters implements ByteOffsetRefactor
{
    public function __construct(
        private Reflector $reflector,
        private AstProvider $parser,
    ) {
    }

    public function refactor(TextDocument $document, ByteOffset $offset): TextEdits
    {
        $node = $this->parser
            ->get($document)
            ->getDescendantNodeAtPosition($offset->toInt())
            ->getFirstAncestor(CallExpression::class, ObjectCreationExpression::class)
        ;

        if (!$node instanceof CallExpression && !$node instanceof ObjectCreationExpression) {
            return TextEdits::none();
        }

        $arguments = $node->argumentExpressionList;
        if (!$arguments instanceof ArgumentExpressionList) {
            return TextEdits::none();
        }

        try {
            $method = $this->reflector->reflectNode($document, $node->getEndPosition());
        } catch (NotFound $notFound) {
            return TextEdits::none();
        }

        if (!$method instanceof ReflectionMethodCall && !$method instanceof ReflectionObjectCreationExpression) {
            return TextEdits::none();
        }

        $methodName = $this->getMethodName($method);
        try {
            $methodDefinition = $method->class()->methods()->get($methodName);
        } catch (NotFound) {
            return TextEdits::none();
        }

        /** @var Generator<int, ArgumentExpression> $value */
        $value = $arguments->getValues();

        $isFirstArgumentNamed = iterator_to_array($value)[0]->name instanceof Token;
        if ($isFirstArgumentNamed) {
            return $this->convertToPositionalArguments($methodDefinition, $arguments, $node);
        }

        return $this->convertToNamedArguments($methodDefinition, $node);
    }

    private function convertToNamedArguments(
        ReflectionMethod $definition,
        CallExpression|ObjectCreationExpression $node,
    ): TextEdits {
        $textEdits = [];

        $parameters = array_keys($this->getArguments($definition));

        /** @var Generator<int, ArgumentExpression>|array{} $arguments */
        $arguments = $node->argumentExpressionList?->getValues()?? [];
        foreach ($arguments as $i => $argument) {
            if (!$argument->expression instanceof Node) {
                continue;
            }

            $name = $parameters[$i] ?? null;
            if ($name === null) {
                return TextEdits::none();
            }

            $textEdits[] = TextEdit::create(
                $argument->getStartPosition(),
                $argument->getEndPosition() - $argument->getStartPosition(),
                $name.': '. $argument->expression->__toString(),
            );
        }

        return TextEdits::fromTextEdits($textEdits);
    }

    private function convertToPositionalArguments(
        ReflectionMethod $methodDefinition,
        ArgumentExpressionList $arguments,
        CallExpression|ObjectCreationExpression $node
    ): TextEdits {
        $setArguments = [];
        /** @var Generator<ArgumentExpression>|array{} $argumentGenerator */
        $argumentGenerator = $arguments->getValues();
        foreach ($argumentGenerator as $argument) {
            if (!$argument->expression instanceof Node) {
                return TextEdits::none();
            }

            $argumentName = $argument->name?->getText($argument->getFileContents());
            if ($argumentName === null) {
                return TextEdits::none();
            }

            $setArguments[$argumentName] = $argument->expression->getText();
        }

        if ($setArguments === []) {
            return TextEdits::none();
        }

        $lastSetArgument = array_key_last($setArguments);

        $argumentValues = [];
        foreach ($this->getArguments($methodDefinition) as $parameterName => $value) {
            if (array_key_exists($parameterName, $setArguments)) {
                $argumentValues[] = $setArguments[$parameterName];
                if ($parameterName === $lastSetArgument) {
                    break;
                }
                continue;
            }

            $argumentValues[] = var_export($value, true);
        }

        // Parenthese are optional on constructor calls, but if we have arguments, we always have to have them.
        Assert::notNull($node->openParen);
        Assert::notNull($node->closeParen);

        return TextEdits::fromTextEdits([TextEdit::create(
            $node->openParen->getEndPosition(),
            $node->closeParen->getStartPosition() - $node->openParen->getEndPosition(),
            join(', ', $argumentValues),
        )]);
    }

    private function getMethodName(ReflectionObjectCreationExpression|ReflectionMethodCall $method): string
    {
        return match(true) {
            $method instanceof ReflectionMethodCall => $method->name(),
            $method instanceof ReflectionObjectCreationExpression => '__construct',
        };
    }

    /** @return array<string, mixed> */
    private function getArguments(ReflectionMethod $method): array
    {
        $parameters = [];
        foreach ($method->parameters() as $parameter) {
            $parameters[$parameter->name()] = $parameter->default()->value();
        }

        return $parameters;
    }
}
