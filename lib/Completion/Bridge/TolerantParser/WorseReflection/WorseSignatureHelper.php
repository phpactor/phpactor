<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\Completion\Core\Exception\CouldNotHelpWithSignature;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Core\ParameterInformation;
use Phpactor\Completion\Core\SignatureHelp;
use Phpactor\Completion\Core\SignatureHelper;
use Phpactor\Completion\Core\SignatureInformation;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;

class WorseSignatureHelper implements SignatureHelper
{
    private Reflector $reflector;

    private Parser $parser;

    private ObjectFormatter $formatter;

    public function __construct(Reflector $reflector, ObjectFormatter $formatter, ?Parser $parser = null)
    {
        $this->reflector = $reflector;
        $this->parser = $parser ?: new Parser();
        $this->formatter = $formatter;
    }

    public function signatureHelp(
        TextDocument $textDocument,
        ByteOffset $offset
    ): SignatureHelp {
        try {
            return $this->doSignatureHelp($textDocument, $offset);
        } catch (NotFound $notFound) {
            throw new CouldNotHelpWithSignature($notFound->getMessage(), 0, $notFound);
        }
    }

    private function doSignatureHelp(TextDocument $textDocument, ByteOffset $offset): SignatureHelp // NOSONAR
    {
        $rootNode = $this->parser->parseSourceFile($textDocument->__toString());
        $nodeAtPosition = $callNode = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        if (
            ($nodeAtPosition instanceof CallExpression || $nodeAtPosition instanceof ObjectCreationExpression)
            && null === $nodeAtPosition->argumentExpressionList
            && null !== $nodeAtPosition->openParen
            && $nodeAtPosition->openParen->getEndPosition() == $offset->toInt()
        ) {
            $argsNode = null;
            $callNode = $nodeAtPosition;
        } elseif ($nodeAtPosition instanceof ArgumentExpressionList) {
            $argsNode = $nodeAtPosition;
            $callNode = $argsNode->parent ?? null;
        } elseif (!$argsNode = $nodeAtPosition->getFirstChildNode(ArgumentExpressionList::class)) {
            $argsNode = $nodeAtPosition->getFirstAncestor(ArgumentExpressionList::class);
            $callNode = $argsNode->parent ?? null;
        }
        
        // current position not inside a call expression
        if (!$callNode && static::isACallExpression($nodeAtPosition)) {
            $callNode = $nodeAtPosition;
            $argsNode = null;
        }

        if (!$callNode) {
            throw new CouldNotHelpWithSignature(sprintf(
                'Could not provide signature for AST node of type "%s"',
                get_class($nodeAtPosition)
            ));
        }

        $position = 0;
        if ($argsNode) {
            /** @var Node $argNode */
            foreach ($argsNode->getChildNodes() as $argNode) {
                if ($argNode->getEndPosition() >= $offset->toInt()) {
                    break;
                }

                ++$position;
            }
        }

        if ($callNode instanceof ObjectCreationExpression) {
            return $this->signatureHelperForObjectCreation($callNode, $position);
        }

        if ($callNode instanceof Attribute) {
            return $this->signatureHelperForAttribute($callNode, $position);
        }

        if (!$callNode instanceof CallExpression) {
            throw new CouldNotHelpWithSignature(sprintf(
                'Could not resolve signature help for "%s"',
                get_class($callNode)
            ));
        }

        $callable = $callNode->callableExpression;

        /** @phpstan-ignore-next-line */
        if ($callable instanceof QualifiedName) {
            return $this->signatureHelpForFunction($callable, $position);
        }
        
        if ($callable instanceof ScopedPropertyAccessExpression) {
            return $this->signatureHelpForScopedPropertyAccess($callable, $callNode, $position);
        }
        
        if ($callable instanceof MemberAccessExpression) {
            $reflectionOffset = $this->reflector->reflectOffset($textDocument, $callable->getEndPosition());
            $symbolContext = $reflectionOffset->symbolContext();
        
            if ($symbolContext->symbol()->symbolType() !== Symbol::METHOD) {
                throw new CouldNotHelpWithSignature(sprintf(
                    'Could not provide signature member type "%s"',
                    $symbolContext->symbol()->symbolType()
                ));
            }
        
            $containerType = $symbolContext->containerType()->classNamedTypes()->firstOrNull();
        
            if (!$containerType instanceof ClassType) {
                throw new CouldNotHelpWithSignature(sprintf(
                    'Container type is not a class: "%s"',
                    $symbolContext->symbol()->name()
                ));
            }
        
            $reflectionClass = $this->reflector->reflectClassLike($containerType->name());
            $reflectionMethod = $reflectionClass->methods()->get($symbolContext->symbol()->name());
        
            return $this->createSignatureHelp($reflectionMethod, $position);
        }
        
        throw new CouldNotHelpWithSignature(sprintf('Could not provide signature for AST node of type "%s"', get_class($callable)));
    }

    private function signatureHelpForFunction(QualifiedName $callable, int $position): SignatureHelp
    {
        $name = $callable->__toString();
        $functionReflection = $this->reflector->reflectFunction($name);

        return $this->createSignatureHelp($functionReflection, $position);
    }

    private function signatureHelperForObjectCreation(ObjectCreationExpression $node, int $position): SignatureHelp
    {
        $name = $node->classTypeDesignator;
        if (!$name instanceof QualifiedName) {
            throw new CouldNotHelpWithSignature(sprintf(
                'Only provide help for qualified names, got "%s"',
                get_class($name)
            ));
        }

        $offset = $this->reflector->reflectOffset($node->getFileContents(), $name->getStartPosition());

        $reflectionClass = $this->reflector->reflectClass($offset->symbolContext()->type()->__toString());
        $constructor = $reflectionClass->methods()->get('__construct');

        return $this->createSignatureHelp($constructor, $position);
    }

    private function createSignatureHelp(ReflectionFunctionLike $functionReflection, int $position): SignatureHelp
    {
        $signatures = [];
        $parameters = [];

        /** @var ReflectionParameter $parameter */
        foreach ($functionReflection->parameters() as $parameter) {
            $formatted = $this->formatter->format($parameter);
            $parameters[] = new ParameterInformation($parameter->name(), $formatted);
        }

        $formatted = $this->formatter->format($functionReflection);
        $signatures[] = new SignatureInformation($formatted, $parameters);

        return new SignatureHelp($signatures, 0, $position);
    }

    private function signatureHelpForScopedPropertyAccess(ScopedPropertyAccessExpression $callable, CallExpression $node, int $position): SignatureHelp
    {
        $scopeResolutionQualifier = $callable->scopeResolutionQualifier;

        if (!$scopeResolutionQualifier instanceof QualifiedName) {
            throw new CouldNotHelpWithSignature(sprintf('Static calls only supported with qualified names'));
        }

        $class = $scopeResolutionQualifier->getResolvedName();
        $reflectionClass = $this->reflector->reflectClass((string) $class);

        $memberName = $callable->memberName;

        if (!$memberName instanceof Token) {
            throw new CouldNotHelpWithSignature('Variable member names not supported');
        }

        $memberName = $memberName->getText($node->getFileContents());
        $reflectionMethod = $reflectionClass->methods()->get((string) $memberName);

        return $this->createSignatureHelp($reflectionMethod, $position);
    }

    private static function isACallExpression(Node $node): bool
    {
        return $node instanceof CallExpression || $node instanceof ObjectCreationExpression;
    }

    private function signatureHelperForAttribute(Attribute $attrNode, int $position): SignatureHelp
    {
        $name = $attrNode->name;
        if (!$name instanceof QualifiedName) {
            throw new CouldNotHelpWithSignature(sprintf(
                'Only provide help for qualified names, got "%s"',
                get_class($name)
            ));
        }

        $offset = $this->reflector->reflectOffset($attrNode->getFileContents(), $name->getStartPosition());

        $reflectionClass = $this->reflector->reflectClass($offset->symbolContext()->type()->__toString());
        $constructor = $reflectionClass->methods()->get('__construct');

        return $this->createSignatureHelp($constructor, $position);
    }
}
