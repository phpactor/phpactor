<?php

namespace Phpactor\CodeTransform\Adapter\TolerantParser\Refactor;

use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\UseVariableName;
use Phpactor\CodeTransform\Domain\SourceCode;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node;
use Phpactor\CodeTransform\Domain\Refactor\RenameVariable;
use Microsoft\PhpParser\FunctionLike;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

class TolerantRenameVariable implements RenameVariable
{
    private Parser $parser;

    public function __construct(?Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
    }

    public function renameVariable(SourceCode $sourceCode, int $offset, string $newName, string $scope = self::SCOPE_FILE): SourceCode
    {
        $sourceNode = $this->sourceNode($sourceCode->__toString());
        $variable = $this->variableNodeFromSource($sourceNode, $offset);
        $scopeNode = $this->scopeNode($variable, $scope);
        $textEdits = $this->textEditsToRename($scopeNode, $variable, $newName);

        return $sourceCode->withSource(TextEdits::fromTextEdits($textEdits)->apply($sourceCode->__toString()));
    }

    private function sourceNode(string $source): SourceFileNode
    {
        return $this->parser->parseSourceFile($source);
    }

    private function variableNodeFromSource(SourceFileNode $sourceNode, int $offset): Node
    {
        $node = $sourceNode->getDescendantNodeAtPosition($offset);

        if (
            false === $node instanceof Variable &&
            false === $node instanceof UseVariableName &&
            false === $node instanceof Parameter
        ) {
            throw new TransformException(sprintf(
                'Expected Variable or Parameter node, got "%s"',
                get_class($node)
            ));
        }

        return $node;
    }

    private function textEditsToRename(Node $scopeNode, Node $variable, string $newName): array
    {
        $textEdits = [];

        if ($textEdit = $this->textEditForRenameFromNode($variable, $scopeNode, $newName)) {
            $textEdits[] = $textEdit;
        }

        /** @var Node $node */
        foreach ($scopeNode->getDescendantNodes() as $node) {
            if (null === $textEdit = $this->textEditForRenameFromNode($variable, $node, $newName)) {
                continue;
            }

            $textEdits[] = $textEdit;
        }

        return $textEdits;
    }

    private function scopeNode(Node $variable, string $scope): Node
    {
        if ($scope === RenameVariable::SCOPE_FILE) {
            return $variable->getRoot();
        }

        if ($variable instanceof UseVariableName) {
            $variable = $variable->getFirstAncestor(MethodDeclaration::class) ?: $variable;
        }

        $scopeNode = $variable->getFirstAncestor(FunctionLike::class, ClassLike::class, SourceFileNode::class);

        if (null === $scopeNode) {
            throw new TransformException(
                'Could not determine scope node, this should not happen as ' .
                'there should always be a SourceFileNode.'
            );
        }

        return $scopeNode;
    }

    private function variableName(Node $variable): string
    {
        if ($variable instanceof Parameter) {
            $name = $variable->variableName->getText($variable->getFileContents());
            return (string)$name;
        }

        return $variable->getText();
    }

    private function textEditForRenameFromNode(Node $variable, Node $node, string $newName): ?TextEdit
    {
        if (
            false === $node instanceof UseVariableName &&
            false === $node instanceof Variable &&
            false === $node instanceof Parameter
        ) {
            return null;
        }

        if ($this->variableName($variable) !== $this->variableName($node)) {
            return null;
        }


        if ($node instanceof Variable || $node instanceof UseVariableName) {
            return TextEdit::create(
                $node->getStartPosition(),
                $node->getEndPosition() - $node->getStartPosition(),
                '$' . $newName
            );
        }

        if ($node instanceof Parameter) {
            /** @var Parameter $node */
            return TextEdit::create(
                $node->variableName->getStartPosition(),
                $node->variableName->getEndPosition() - $node->variableName->getStartPosition(),
                '$' . $newName
            );
        }
    }
}
