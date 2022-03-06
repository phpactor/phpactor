<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\NodeHelper;
use Phpactor\CodeBuilder\Domain\Prototype\Parameter as PhpactorParameter;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Domain\Prototype\ClassLikePrototype;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\CodeBuilder\Domain\Prototype\ReturnType;
use Phpactor\TextDocument\TextEdit;
use Phpactor\WorseReflection\Core\Util\QualifiedNameListUtil;

abstract class AbstractMethodUpdater
{
    /**
     * @var Renderer
     */
    private $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function updateMethods(Edits $edits, ClassLikePrototype $classPrototype, ClassLike $classNode): void
    {
        if (count($classPrototype->methods()) === 0) {
            return;
        }

        $lastMember = $this->memberDeclarationsNode($classNode)->openBrace;
        $newLine = false;
        $memberDeclarations = $this->memberDeclarations($classNode);
        $existingMethodNames = [];
        $existingMethods = [];
        foreach ($memberDeclarations as $memberNode) {
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

            if ($methodPrototype->body()->lines()->count()) {
                $bodyNode = $methodDeclaration->compoundStatementOrSemicolon;
                $this->appendLinesToMethod($edits, $methodPrototype, $bodyNode);
            }

            if (false === $methodPrototype->applyUpdate() || $this->prototypeSameAsDeclaration($methodPrototype, $methodDeclaration)) {
                $ignoreMethods[] = $methodPrototype->name();
                continue;
            }

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

    abstract protected function memberDeclarations(ClassLike $classNode);

    abstract protected function memberDeclarationsNode(ClassLike $classNode);

    abstract protected function renderMethod(Renderer $renderer, Method $method);

    private function appendLinesToMethod(Edits $edits, Method $method, Node $bodyNode): void
    {
        if (false === $bodyNode instanceof CompoundStatementNode) {
            return;
        }

        $lastStatement = end($bodyNode->statements) ?: $bodyNode->openBrace;

        foreach ($method->body()->lines() as $line) {
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
        if ($methodDeclaration->parameters) {
            $renderedParameters = array_combine(
                array_map(function (Parameter $parameter) {
                    return substr($parameter->variableName ? $parameter->variableName->getText($parameter->getFileContents()) : false, 1);
                }, iterator_to_array($methodDeclaration->parameters->getElements())),
                array_map(function (Parameter $parameter) {
                    return $parameter->getText();
                }, iterator_to_array($methodDeclaration->parameters->getElements()))
            );
        }

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

        $returnType = (string) $returnType;

        if (!$methodDeclaration->returnTypeList) {
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

    private function prototypeSameAsDeclaration(Method $methodPrototype, MethodDeclaration $methodDeclaration)
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

                $type = $parameterPrototype->type();

                // adding a parameter type
                if (null === $parameter->typeDeclarationList && $type->notNone()) {
                    return false;
                }

                // if parameter has a different type
                if (null !== $parameter->typeDeclarationList) {
                    $typeName = $parameter->typeDeclarationList->getText($methodDeclaration->getFileContents());
                    if ($type->notNone() && (string) $type !== $typeName) {
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
}
