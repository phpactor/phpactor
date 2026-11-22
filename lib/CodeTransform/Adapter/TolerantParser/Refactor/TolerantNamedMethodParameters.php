<?php

declare(strict_types=1);

namespace Phpactor\CodeTransform\Adapter\TolerantParser\Refactor;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Token;
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
        ;
        $node = $node instanceof CallExpression ? $node : $node->getFirstAncestor(CallExpression::class);

        if (!$node instanceof CallExpression) {
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

        if (!$method instanceof ReflectionMethodCall) {
            return TextEdits::none();
        }
        try {
            $methodDefinition = $method->class()->methods()->get($method->name());
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

    private function convertToNamedArguments(ReflectionMethod $methodDefinition, CallExpression $node): TextEdits
    {
        $textEdits = [];

        $parameters = $methodDefinition->parameters();

        /** @var Generator<int, ArgumentExpression>|array{} $arguments */
        $arguments = $node->argumentExpressionList?->getValues()?? [];
        foreach ($arguments as $i => $argument) {
            if (!$argument->expression instanceof Node) {
                continue;
            }

            $name = $parameters->at($i)?->name();
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
        CallExpression $node
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
        foreach ($methodDefinition->parameters() as $parameter) {
            $parameterName = $parameter->name();

            if (array_key_exists($parameterName, $setArguments)) {
                $argumentValues[] = $setArguments[$parameterName];
                if ($parameterName === $lastSetArgument) {
                    break;
                }
                continue;
            }

            $value = $parameter->default()->value();
            if ($value === null) {
                return TextEdits::none();
            }
            $argumentValues[] = var_export($value, true);

        }

        return TextEdits::fromTextEdits([TextEdit::create(
            $node->openParen->getEndPosition(),
            $node->closeParen->getStartPosition() - $node->openParen->getEndPosition(),
            join(', ', $argumentValues),
        )]);
    }
}
