<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ParameterDeclarationList;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\NodeHelper;
use Phpactor\CodeBuilder\Domain\Prototype\ClassLikePrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Phpactor\CodeBuilder\Domain\Prototype\Parameter as PhpactorParameter;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Domain\Prototype\ReturnType;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\TextDocument\TextEdit;
use Phpactor\WorseReflection\Core\Util\QualifiedNameListUtil;

/**
 * @template TMembersNodeType of Node
 */
abstract class AbstractMethodUpdater
{
    public function __construct(private Renderer $renderer)
    {
    }

    public function updateMethods(Edits $edits, ClassLikePrototype $classPrototype, ClassLike $classNode): void
    {
        if (count($classPrototype->methods()) === 0) {
            return;
        }

        $lastMember = $this->memberDeclarationsNode($classNode)->openBrace;
        $newLine = false;
        $existingMethodNames = [];
        $existingMethods = [];
        foreach ($this->memberDeclarations($classNode) as $memberNode) {
            if ($memberNode instanceof PropertyDeclaration) {
                $lastMember = $memberNode;
                $newLine = true;
            }

            if ($memberNode instanceof MethodDeclaration) {
                $lastMember = $memberNode;
                $existingMethodNames[] = $memberNode->getName();
                $existingMethods[$memberNode->getName()] = $memberNode;
                $newLine = true;
            }
        }

        // Update methods
        $methodPrototypes = $classPrototype->methods()->in($existingMethodNames);

        $ignoreMethods = [];
        foreach ($methodPrototypes as $methodPrototype) {
            /** @var MethodDeclaration $methodDeclaration */
            $methodDeclaration = $existingMethods[$methodPrototype->name()];

            if ($methodPrototype->docblock()->notNone()) {
                $this->updateDocblock($edits, $methodPrototype, $methodDeclaration);
            }

            $lines = $methodPrototype->body()->lines();
            if ($lines !== null && $lines->count()) {
                $bodyNode = $methodDeclaration->compoundStatementOrSemicolon;
                $this->appendLinesToMethod($edits, $methodPrototype, $bodyNode);
            }

            if (false === $methodPrototype->applyUpdate() || $this->prototypeSameAsDeclaration($methodPrototype, $methodDeclaration)) {
                $ignoreMethods[] = $methodPrototype->name();
                continue;
            }

            /** @phpstan-ignore-next-line */
            if ($methodPrototype->applyUpdate()) {
                $this->updateOrAddParameters($edits, $methodPrototype->parameters(), $methodDeclaration);
                $this->updateOrAddReturnType($edits, $methodPrototype->returnType(), $methodDeclaration);
            }
        }

        // Add methods
        $methodPrototypes = $classPrototype->methods()->notIn($existingMethodNames)->notIn($ignoreMethods);

        if (0 === count($methodPrototypes)) {
            return;
        }

        if ($newLine) {
            $edits->after($lastMember, PHP_EOL);
        }

        foreach ($methodPrototypes as $methodPrototype) {
            $edits->after(
                $lastMember,
                PHP_EOL . $edits->indent($this->renderMethod($this->renderer, $methodPrototype), 1)
            );

            if (false === $classPrototype->methods()->isLast($methodPrototype)) {
                $edits->after($lastMember, PHP_EOL);
            }
        }
    }

    /**
    * @return array<Node>
    */
    abstract protected function memberDeclarations(ClassLike $classNode): array;

    /** @return TMembersNodeType */
    abstract protected function memberDeclarationsNode(ClassLike $classNode);

    abstract protected function renderMethod(Renderer $renderer, Method $method): string;

    private function appendLinesToMethod(Edits $edits, Method $method, Node $bodyNode): void
    {
        if (false === $bodyNode instanceof CompoundStatementNode) {
            return;
        }

        $lastStatement = end($bodyNode->statements) ?: $bodyNode->openBrace;

        foreach ($method->body()->lines() ?? [] as $line) {
            // do not add duplicate lines
            $bodyNodeLines = explode(PHP_EOL, $bodyNode->getText());

            foreach ($bodyNodeLines as $bodyNodeLine) {
                if (trim($bodyNodeLine) == trim((string) $line)) {
                    continue 2;
                }
            }

            $edits->after(
                $lastStatement,
                PHP_EOL . $edits->indent((string) $line, 2)
            );
        }
    }

    private function updateOrAddParameters(Edits $edits, Parameters $parameters, MethodDeclaration $methodDeclaration): void
    {
        if (0 === $parameters->count()) {
            return;
        }

        $renderedParameters = [];

        /** @var ParameterDeclarationList|null $existingParameterDeclaration */
        $existingParameterDeclaration = $methodDeclaration->parameters;

        // Copying over existing parameters
        if ($existingParameterDeclaration) {
            /** @var array<Parameter> $existingParameters */
            $existingParameters = iterator_to_array($existingParameterDeclaration->getElements());

            // This is an array [variableName => 'rendered parameter node as string']
            $renderedParameters = (array)array_combine(
                array_map(function (Parameter $parameter) {
                    $variableName = $parameter->variableName ?
                        $parameter->variableName->getText($parameter->getFileContents()):
                        false;
                    return substr((string) $variableName, 1);
                }, $existingParameters),
                array_map(fn (Parameter $parameter) => $parameter->getText(), $existingParameters)
            );
        }

        // Adding new parameters to the mix
        foreach ($parameters as $parameter) {
            assert($parameter instanceof PhpactorParameter);
            if (!isset($renderedParameters[$parameter->name()])) {
                $renderedParameters[$parameter->name()] = $this->renderer->render($parameter);
            }
        }

        $startPosition = $methodDeclaration->openParen->getStartPosition();
        $edits->add(TextEdit::create(
            $startPosition + 1,
            $methodDeclaration->closeParen->getStartPosition() - $startPosition - 1,
            implode(', ', $renderedParameters)
        ));
    }

    private function updateOrAddReturnType(Edits $edits, ReturnType $returnType, MethodDeclaration $methodDeclaration): void
    {
        if (false === $returnType->notNone()) {
            return;
        }

        $returnType = (string) $this->renderer->render($returnType->type());

        if (!$methodDeclaration->returnTypeList && trim($returnType)) {
            $edits->after($methodDeclaration->closeParen, ': ' . $returnType);
            return;
        }

        $firstReturnType = QualifiedNameListUtil::firstQualifiedNameOrNullOrToken($methodDeclaration->returnTypeList);

        if (null === $firstReturnType) {
            return;
        }

        $existingReturnType = $returnType ? NodeHelper::resolvedShortName($methodDeclaration, $firstReturnType) : null;

        if (null === $existingReturnType) {
            // TODO: Add return type
            return;
        }

        if ($returnType === $existingReturnType) {
            return;
        }

        $edits->replace($firstReturnType, ' ' . $returnType);
    }

    private function prototypeSameAsDeclaration(Method $methodPrototype, MethodDeclaration $methodDeclaration): bool
    {
        $parameters = [];
        if (null !== $methodDeclaration->parameters) {
            $parameters = array_filter($methodDeclaration->parameters->children, function ($parameter) {
                return $parameter instanceof Parameter;
            });

            /** @var Parameter $parameter */
            foreach ($parameters as $parameter) {
                $name = ltrim((string)$parameter->variableName->getText($methodDeclaration->getFileContents()), '$');

                // if method prototype doesn't have the existing parameter
                if (false === $methodPrototype->parameters()->has($name)) {
                    return false;
                }

                $parameterPrototype = $methodPrototype->parameters()->get($name);

                $type = (string)$this->renderer->render($parameterPrototype->type());

                // adding a parameter type
                if (null === $parameter->typeDeclarationList && $type) {
                    return false;
                }

                // if parameter has a different type
                if (null !== $parameter->typeDeclarationList) {
                    $typeName = $parameter->typeDeclarationList->getText($methodDeclaration->getFileContents());
                    if ($type && (string) $type !== $typeName) {
                        return false;
                    }
                }
            }
        }

        // method prototype has all of the parameters, but does it have extra ones?
        if ($methodPrototype->parameters()->count() !== count($parameters)) {
            return false;
        }

        // are we adding a return type?
        if ($methodPrototype->returnType()->notNone() && null === $methodDeclaration->returnTypeList) {
            return false;
        }

        // is the return type the same?
        if (null !== $methodDeclaration->returnTypeList) {
            // TODO: Does this work?
            $name = $methodDeclaration->returnTypeList->getText();
            if ($methodPrototype->returnType()->__toString() !== $name) {
                return false;
            }
        }

        return true;
    }

    private function updateDocblock(Edits $edits, Method $methodPrototype, MethodDeclaration $methodDeclaration): void
    {
        $edits->add(TextEdit::create(
            $methodDeclaration->getFullStartPosition(),
            strlen($methodDeclaration->getLeadingCommentAndWhitespaceText()),
            $methodPrototype->docblock()->__toString()
        ));
    }
}
