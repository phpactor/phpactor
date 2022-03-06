<?php

namespace Phpactor\WorseReferenceFinder;

use Generator;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\FunctionLike;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\CatchClause;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\UseVariableName;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use function assert;
use Exception;

class TolerantVariableReferenceFinder implements ReferenceFinder
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var bool
     */
    private $includeDefinition;

    public function __construct(Parser $parser, bool $includeDefinition = false)
    {
        $this->parser = $parser;
        $this->includeDefinition = $includeDefinition;
    }
    /**
     * {@inheritDoc}
     */
    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Generator
    {
        $sourceNode = $this->sourceNode($document->__toString());
        $variable = $this->variableNodeFromSource($sourceNode, $byteOffset->toInt());
        if ($variable === null) {
            return;
        }
        
        $scopeNode = $this->scopeNode($variable);
        $referencesGenerator = $this->find($scopeNode, $this->variableName($variable), $document->uri());

        if (false === $this->includeDefinition) {
            $referencesGenerator->next();
        }

        if ($referencesGenerator->valid()) {
            yield from $referencesGenerator;
        }
    }

    private function sourceNode(string $source): SourceFileNode
    {
        return $this->parser->parseSourceFile($source);
    }

    private function variableNodeFromSource(SourceFileNode $sourceNode, int $offset): ?Node
    {
        $node = $sourceNode->getDescendantNodeAtPosition($offset);

        if (
            false === $node instanceof Variable &&
            false === $node instanceof UseVariableName &&
            false === $node instanceof Parameter &&
            false === $node instanceof CatchClause
        ) {
            return null;
        }

        if (
            ($node instanceof Variable && $node->parent instanceof ScopedPropertyAccessExpression)
            || ($node instanceof Variable && $node->getFirstAncestor(PropertyDeclaration::class))
        ) {
            return null;
        }

        return $node;
    }

    private function scopeNode(Node $variable): Node
    {
        if ($variable instanceof CatchClause) {
            return $variable;
        }

        $name = $this->variableName($variable);
        if ($variable instanceof UseVariableName) {
            $variable = $variable->getFirstAncestor(MethodDeclaration::class) ?: $variable;
        }

        $scopeNode = $variable->getFirstAncestor(FunctionLike::class, ClassLike::class, SourceFileNode::class, CatchClause::class);
        while (
            $scopeNode instanceof AnonymousFunctionCreationExpression &&
            $this->nameExistsInUseClause($name, $scopeNode)
        ) {
            $scopeNode = $scopeNode->getFirstAncestor(FunctionLike::class, ClassLike::class, SourceFileNode::class, CatchClause::class);
        }

        if (null === $scopeNode) {
            throw new Exception(
                'Could not determine scope node, this should not happen as ' .
                'there should always be a SourceFileNode.'
            );
        }

        return $scopeNode;
    }

    private function nameExistsInUseClause(string $variableName, AnonymousFunctionCreationExpression $function): bool
    {
        if (
            $function->anonymousFunctionUseClause === null
            || $function->anonymousFunctionUseClause->useVariableNameList === null
            || $function->anonymousFunctionUseClause->useVariableNameList instanceof MissingToken
        ) {
            return false;
        }

        foreach ($function->anonymousFunctionUseClause->useVariableNameList->getElements() as $useVariableName) {
            assert($useVariableName instanceof UseVariableName);
            if ($this->variableName($useVariableName) == $variableName) {
                return true;
            }
        }
        return false;
    }
    /**
     * @return Generator<PotentialLocation>
     */
    private function find(Node $scopeNode, string $name, string $uri): Generator
    {
        if ($scopeNode instanceof CatchClause && $scopeNode->variableName instanceof Token && $name == substr((string)$scopeNode->variableName->getText($scopeNode->getFileContents()), 1)) {
            yield PotentialLocation::surely(
                Location::fromPathAndOffset($uri, $scopeNode->variableName->start)
            );
        }

        /** @var Node $node */
        foreach ($scopeNode->getChildNodes() as $node) {
            if ($node instanceof AnonymousFunctionCreationExpression && !$this->nameExistsInUseClause($name, $node)) {
                continue;
            }
            
            if ($node instanceof Variable && $name == $node->getName()) {
                yield PotentialLocation::surely(
                    Location::fromPathAndOffset($uri, $node->getStartPosition())
                );
                continue;
            }

            if ($node instanceof Parameter && $name == $node->getName()) {
                $variableName = $node->variableName;
                if (!$variableName instanceof Token) {
                    continue;
                }
                yield PotentialLocation::surely(
                    Location::fromPathAndOffset($uri, $variableName->start)
                );
                continue;
            }

            if ($node instanceof UseVariableName && $name == $node->getName()) {
                yield PotentialLocation::surely(
                    Location::fromPathAndOffset($uri, $node->getStartPosition())
                );
                continue;
            }

            yield from $this->find($node, $name, $uri);
        }
    }

    private function isPotentialReferenceNode(Node $node): bool
    {
        return
            $node instanceof UseVariableName
            || $node instanceof Variable
            || $node instanceof Parameter
            || $node instanceof CatchClause
        ;
    }

    private function variableName(Node $variable): ?string
    {
        if (
            $variable instanceof Variable ||
            $variable instanceof UseVariableName ||
            $variable instanceof Parameter
        ) {
            return $variable->getName();
        }

        if ($variable instanceof CatchClause && $variable->variableName) {
            return substr((string)$variable->variableName->getText($variable->getFileContents()), 1);
        }
        
        return null;
    }
}
