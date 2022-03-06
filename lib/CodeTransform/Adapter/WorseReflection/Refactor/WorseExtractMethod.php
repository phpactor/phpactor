<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\FunctionLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\CatchClause;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\WorseReflection\Reflector;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Code;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\Utils\TextUtils;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use function iterator_to_array;
use function prev;

class WorseExtractMethod implements ExtractMethod
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var Updater
     */
    private $updater;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var BuilderFactory
     */
    private $factory;

    public function __construct(Reflector $reflector, BuilderFactory $factory, Updater $updater, Parser $parser = null)
    {
        $this->reflector = $reflector;
        $this->updater = $updater;
        $this->parser = $parser ?: new Parser();
        $this->factory = $factory;
    }

    public function canExtractMethod(SourceCode $source, int $offsetStart, int $offsetEnd): bool
    {
        if ($offsetEnd == $offsetStart || $offsetEnd - $offsetStart === strlen($source->__toString())) {
            return false;
        }
        $node = $this->parser->parseSourceFile($source->__toString());
        $endNode = $node->getDescendantNodeAtPosition($offsetEnd);
        $startNode = $node->getDescendantNodeAtPosition($offsetStart);

        if (
            $endNode instanceof CompoundStatementNode &&
            $endNode->openBrace->getEndPosition() < $offsetEnd &&
            $endNode->closeBrace->getStartPosition() >= $offsetEnd &&
            count($endNode->statements) > 0
        ) {
            $stmt = end($endNode->statements);
            assert($stmt instanceof Node);
            while ($stmt && $stmt->getEndPosition() > $offsetEnd) {
                $stmt = prev($endNode->statements);
            }
            
            $endNode = $stmt ?? $endNode;
            assert($endNode instanceof Node);
        }

        while ($endNode->parent && !($endNode->parent instanceof CompoundStatementNode)) {
            $endNode = $endNode->parent;
        }

        if (
            $startNode instanceof CompoundStatementNode &&
            $startNode->openBrace->getEndPosition() <= $offsetStart &&
            $startNode->closeBrace->getStartPosition() > $offsetStart &&
            count($startNode->statements) > 0
        ) {
            $stmt = current($startNode->statements);
            assert($stmt instanceof Node);
            while ($stmt && $stmt->getStartPosition() < $offsetStart) {
                $stmt = next($startNode->statements);
            }
            
            $startNode = $stmt ?? $startNode;
            assert($startNode instanceof Node);
        }

        while ($startNode->parent && !($startNode->parent instanceof CompoundStatementNode)) {
            $startNode = $startNode->parent;
        }

        if ($startNode->parent != $endNode->parent) {
            return false;
        }

        return true;
    }

    public function extractMethod(SourceCode $source, int $offsetStart, int $offsetEnd, string $name): TextDocumentEdits
    {
        if (!$this->canExtractMethod($source, $offsetStart, $offsetEnd)) {
            throw new TransformException('Cannot extract method. Check if start and end statements are in different scopes.');
        }

        $isExpression = $this->isSelectionAnExpression($source, $offsetStart, $offsetEnd);

        $selection = $source->extractSelection($offsetStart, $offsetEnd);
        $builder = $this->factory->fromSource($source);
        $reflectionMethod = $this->reflectMethod($offsetEnd, $source, $name);

        $methodBuilder = $this->createMethodBuilder($reflectionMethod, $builder, $name);
        $newMethodBody = $this->removeIndentation($selection);
        if ($isExpression) {
            $newMethodBody = $this->addExpressionReturn($newMethodBody, $source, $offsetEnd, $methodBuilder);
        }
        $methodBuilder->body()->line($newMethodBody);

        $locals = $this->scopeLocalVariables($source, $offsetStart, $offsetEnd);

        $parameterVariables = $this->parameterVariables($locals->lessThan($offsetStart), $selection, $offsetStart);
        $args = $this->addParametersAndGetArgs($parameterVariables, $methodBuilder, $builder);

        $returnVariables = $this->returnVariables($locals, $reflectionMethod, $source, $offsetStart, $offsetEnd);
        
        $returnAssignment = $this->addReturnAndGetAssignment(
            $returnVariables,
            $methodBuilder,
            $args
        );
        
        $prototype = $builder->build();

        $replacement = $this->replacement($name, $args, $selection, $returnAssignment);

        if ($isExpression) {
            $replacement = rtrim($replacement, ';');
        }

        return new TextDocumentEdits(
            TextDocumentUri::fromString($source->path()),
            $this->updater->textEditsFor($prototype, Code::fromString((string) $source))
                ->add(TextEdit::create($offsetStart, $offsetEnd - $offsetStart, $replacement))
        );
    }

    private function parameterVariables(Assignments $locals, string $selection, int $offsetStart): array
    {
        $variableNames = $this->variableNames($selection);

        $parameterVariables = [];
        foreach ($variableNames as $variable) {
            $variables = $locals->lessThanOrEqualTo($offsetStart)->byName($variable);
            if ($variables->count()) {
                $parameterVariables[$variable] = $variables->last();
            }
        }

        return $parameterVariables;
    }

    private function returnVariables(Assignments $locals, ReflectionMethod $reflectionMethod, string $source, int $offsetStart, int $offsetEnd): array
    {
        // variables that are:
        //
        // - defined in the selection
        // - and used in the parent scope
        // - after the end offset
        $tailDependencies = $this->variableNames(
            $tail = mb_substr(
                $source,
                $offsetEnd,
                $reflectionMethod->position()->end() - $offsetEnd
            )
        );

        $returnVariables = [];
        foreach ($tailDependencies as $variable) {
            $variables = $locals->byName($variable)
                ->lessThanOrEqualTo($offsetEnd)
                ->greaterThanOrEqualTo($offsetStart);

            if ($variables->count()) {
                $returnVariables[$variable] = $variables->last();
            }
        }

        return $returnVariables;
    }

    private function removeIndentation(string $selection): string
    {
        return TextUtils::removeIndentation($selection);
    }

    private function createMethodBuilder(ReflectionMethod $reflectionMethod, SourceCodeBuilder $builder, string $name): MethodBuilder
    {
        $methodBuilder = $builder->class(
            $reflectionMethod->class()->name()->short()
        )->method($name);
        $methodBuilder->visibility('private');

        return $methodBuilder;
    }

    private function reflectMethod(int $offsetEnd, string $source, string $name): ReflectionMethod
    {
        $offset = $this->reflector->reflectOffset($source, $offsetEnd);
        $thisVariable = $offset->frame()->locals()->byName('this');

        if (empty($thisVariable)) {
            throw new TransformException('Cannot extract method, not in class scope');
        }

        $className = $thisVariable->last()->symbolContext()->type()->className();

        if (!$className) {
            throw new TransformException('Cannot extract method, not in class scope');
        }

        $reflectionClass = $this->reflector->reflectClass((string) $className);

        $methods = $reflectionClass->methods();
        if ($methods->belongingTo($className)->has($name)) {
            throw new TransformException(sprintf('Class "%s" already has method "%s"', (string) $className, $name));
        }

        // returns the method that the offset is within
        $member = $methods->belongingTo($className)->atOffset($offsetEnd)->first();

        if (!$member instanceof ReflectionMethod) {
            throw new TransformException(sprintf('Member should have been a method but it was a "%s"', get_class($member)));
        }

        return $member;
    }

    private function addParametersAndGetArgs(array $freeVariables, MethodBuilder $methodBuilder, SourceCodeBuilder $builder): array
    {
        $args = [];

        /** @var Variable $freeVariable */
        foreach ($freeVariables as $freeVariable) {
            if (in_array($freeVariable->name(), [ 'this', 'self' ])) {
                continue;
            }

            $parameterBuilder = $methodBuilder->parameter($freeVariable->name());
            $variableType = $freeVariable->symbolContext()->types()->best();
            if ($variableType->isDefined()) {
                $prefix = $variableType->isNullable() ? '?' : '';
                $parameterBuilder->type($prefix.$variableType->short());
                if ($variableType->isClass()) {
                    $builder->use((string) $variableType);
                }
            }

            $args[] = '$' . $freeVariable->name();
        }

        return $args;
    }

    private function scopeLocalVariables(SourceCode $source, int $offsetStart, int $offsetEnd): Assignments
    {
        return $this->reflector->reflectOffset(
            (string) $source,
            $offsetEnd
        )->frame()->locals();
    }

    private function variableNames(string $source): array
    {
        $node = $this->parseSelection($source);
        $variables = $this->extractVariableNamesFromNode($node, []);
        return array_values($variables);
    }

    private function extractVariableNamesFromNode(Node $node, array $ignoreNames): array
    {
        $fileContents = $node->getFileContents();
        if ($node instanceof CatchClause && $node->variableName !== null) {
            $ignoreNames[] = $node->variableName->getText($fileContents);
        }
        $variables = [];
        foreach ($node->getChildNodesAndTokens() as $nodeOrToken) {
            if ($nodeOrToken instanceof FunctionLike) {
                continue;
            }

            if ($nodeOrToken instanceof Node) {
                $variables = array_merge($variables, $this->extractVariableNamesFromNode($nodeOrToken, $ignoreNames));
                continue;
            }

            if ($nodeOrToken->kind == TokenKind::VariableName) {
                $text = $nodeOrToken->getText($fileContents);
                if (is_string($text) && !in_array($text, $ignoreNames)) {
                    $name = substr($text, 1);
                    $variables[$name] = $name;
                }
            }
        }
        return $variables;
    }

    private function addReturnAndGetAssignment(array $returnVariables, MethodBuilder $methodBuilder, array $args): ?string
    {
        $returnVariables = array_filter($returnVariables, function (Variable $variable) {
            return false === in_array($variable->name(), ['self', 'this']);
        });

        $returnVariables = array_filter($returnVariables, function (Variable $variable) use ($args) {
            if ($variable->symbolContext()->type()->isPrimitive()) {
                return true;
            }

            return false === in_array('$' . $variable->name(), $args);
        });

        if (empty($returnVariables)) {
            return null;
        }

        if (count($returnVariables) === 1) {
            /** @var Variable $variable */
            $variable = reset($returnVariables);
            $methodBuilder->body()->line('return $' . $variable->name() . ';');
            $type = $variable->symbolContext()->types()->best();
            if ($type->isDefined()) {
                $prefix = $type->isNullable() ? '?' : '';
                $className = $type->className();
                $methodBuilder->returnType($prefix.$type->short());
                if ($className) {
                    $methodBuilder->end()->end()->use($className->full());
                }
            }

            return '$' . $variable->name();
        }

        $names = implode(', ', array_map(function (Variable $variable) {
            return '$' . $variable->name();
        }, $returnVariables));

        $methodBuilder->body()->line('return [' . $names . '];');
        $methodBuilder->returnType('array');

        return 'list(' . $names . ')';
    }

    private function replacement(string $name, array $args, string $selection, ?string $returnAssignment): string
    {
        $indentation = str_repeat(' ', TextUtils::stringIndentation($selection));
        $callString = '$this->'  . $name . '(' . implode(', ', $args) . ');';

        if (empty($returnAssignment)) {
            $replacement = $indentation . $callString;
            $selectionRootNode = $this->parseSelection($selection);

            if ($this->nodeContainsReturnStatement($selectionRootNode)) {
                $replacement = 'return ' . $replacement;
            }

            return $replacement;
        }

        return $indentation . $returnAssignment . ' = ' . $callString;
    }

    private function nodeContainsReturnStatement(Node $node): bool
    {
        foreach ($node->getDescendantNodes(
            function (Node $n) {
                return !($n instanceof FunctionLike);
            }
        ) as $node) {
            if ($node instanceof ReturnStatement) {
                return true;
            }
        }
        return false;
    }

    private function isSelectionAnExpression(SourceCode $source, int $offsetStart, int $offsetEnd): bool
    {
        $node = $this->parser->parseSourceFile($source->__toString());
        $endNode = $node->getDescendantNodeAtPosition($offsetEnd);
        
        // end node is in the statement body, get last child node
        if ($endNode instanceof CompoundStatementNode) {
            $childNodes = iterator_to_array($endNode->getChildNodes());
            $endNode = end($childNodes);
            assert($endNode instanceof Node);
        }
        
        // get the positional parent of the node
        while ($endNode->parent && $endNode->getEndPosition() === $endNode->parent->getEndPosition()) {
            $endNode = $endNode->parent;
        }
        
        return !$endNode->parent instanceof CompoundStatementNode;
    }

    private function addExpressionReturn(string $newMethodBody, SourceCode $source, int $offsetEnd, MethodBuilder $methodBuilder): string
    {
        $newMethodBody = 'return ' . $newMethodBody .';';
        $offset = $this->reflector->reflectOffset($source->__toString(), $offsetEnd);
        $expressionTypes = $offset->symbolContext()->types();
        if ($expressionTypes->count() === 1) {
            $type = $expressionTypes->best();
            if ($type->isDefined()) {
                $methodBuilder->returnType($type->short());
            }
            $className = $type->className();
            if ($className) {
                $methodBuilder->end()->end()->use($className->full());
            }
        }
        return $newMethodBody;
    }

    private function parseSelection(string $source): SourceFileNode
    {
        $source = '<?php ' . $source;
        $node = $this->parser->parseSourceFile($source);
        return $node;
    }
}
