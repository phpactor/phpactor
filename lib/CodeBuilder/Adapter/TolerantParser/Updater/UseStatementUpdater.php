<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\ImportedNames;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\NodeHelper;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatement;

class UseStatementUpdater
{
    public function updateUseStatements(Edits $edits, SourceCode $prototype, SourceFileNode $node): void
    {
        if (0 === count($prototype->useStatements())) {
            return;
        }

        $startNode = $node;
        foreach ($node->getChildNodes() as $childNode) {
            if ($childNode instanceof InlineHtml) {
                $startNode = $node->getFirstChildNode(InlineHtml::class);
            }
            if ($childNode instanceof NamespaceDefinition) {
                $startNode = $childNode;
            }
            if ($childNode instanceof NamespaceUseDeclaration) {
                $startNode = $childNode;
            }
        }

        $bodyNode = null;
        foreach ($node->getChildNodes() as $childNode) {
            if ($childNode->getStartPosition() > $startNode->getStartPosition()) {
                $bodyNode = $childNode;
                break;
            }
        }

        $usePrototypes = $this->resolveUseStatements($prototype, $startNode);

        if (empty($usePrototypes)) {
            return;
        }

        // When adding after the namespace definition the text is added before the new line
        // And when adding after the php declaration the text is added after the new line
        // Examples:
        // $startNode->getText(); // Returns: namespace Test;\n
        // $edits->after($startNode, 'TOTO'); // Result: namespace Test;TOTO\n
        // $startNode->getText(); // Returns: <?php\n
        // $edits->after($startNode, 'TOTO'); // Result: <?php\n
        //                                               TOTO

        if ($startNode instanceof NamespaceDefinition) {
            // Add a new line to be in the same case that if it was an InlineHtml node
            $edits->after($startNode, PHP_EOL);
        }

        foreach ($usePrototypes as $usePrototype) {
            $prototypeOrder = $usePrototype->type() === UseStatement::TYPE_FUNCTION ? '1' : '0';
            $editText = $this->buildEditText($usePrototype);

            foreach ($node->getChildNodes() as $childNode) {
                if ($childNode instanceof NamespaceUseDeclaration) {
                    /** @phpstan-ignore-next-line */
                    if (!$childNode->useClauses) {
                        continue;
                    }
                    foreach ($childNode->useClauses->getElements() as $useClause) {
                        assert($useClause instanceof NamespaceUseClause);

                        $nodeOrder = $childNode->functionOrConst !== null ? '1' : '0';
                        // try to find the first lexicographycally greater use
                        // statement and insert before if there is one
                        $cmp = strcmp(
                            $nodeOrder.$useClause->namespaceName->getText(),
                            $prototypeOrder.$usePrototype->__toString()
                        );
                        if ($cmp === 0) {
                            continue 3;
                        }
                        if ($cmp > 0) {
                            // Add before one of the use import and add a new
                            // line so the new import is on its own line
                            $edits->before($childNode, $editText . PHP_EOL);
                            continue 3;
                        }
                    }
                }
            }

            // Either add after the NamespaceUseDeclaration node if there
            // already was use imports or after the namespace/php declaration
            // Since it will add before the lasts new line of the node we
            // preprend with another one so that the use statement is on its
            // own line
            $newUseStatement = PHP_EOL . $editText;
            $edits->after($startNode, $newUseStatement);
        }

        if ($startNode instanceof InlineHtml) {
            // Add a new line after the last use statement so that it's on its
            // own line
            $edits->after($startNode, PHP_EOL);
        }

        // Add another new line to separate the new use declaration from
        // the code that follow
        if (
            !$startNode instanceof NamespaceUseDeclaration &&
            $bodyNode && NodeHelper::emptyLinesPrecedingNode($bodyNode) === 0
        ) {
            $edits->after($startNode, PHP_EOL);
        }
    }

    /**
     * @return UseStatement[]
     */
    private function resolveUseStatements(SourceCode $prototype, Node $lastNode): array
    {
        $usePrototypes = $this->filterExisting($lastNode, $prototype);
        $usePrototypes = $this->filterSameNamespace($lastNode, $usePrototypes);

        return $usePrototypes;
    }

    /**
     * @return list<UseStatement>
     */
    private function filterExisting(Node $lastNode, SourceCode $prototype): array
    {
        $existingNames = new ImportedNames($lastNode);
        $usePrototypes = $prototype->useStatements()->sorted();

        $usePrototypes = array_filter(iterator_to_array($usePrototypes), function (UseStatement $usePrototype) use ($existingNames) {
            $existing = $usePrototype->type() === UseStatement::TYPE_FUNCTION ?
                $existingNames->functionNames() :
                $existingNames->classNames();

            $candidate = $usePrototype->hasAlias() ? $usePrototype->alias() : $usePrototype->name()->__toString();

            // when we are dealing with aliases, they are stored in the array
            // keys...
            $existing = $usePrototype->hasAlias() ? array_keys($existing) : array_values($existing);

            return false === in_array(
                $candidate,
                $existing,
                true
            );
        });

        return $usePrototypes;
    }
    /**
     * @param array<UseStatement> $usePrototypes
     * @return list<UseStatement>
     */
    private function filterSameNamespace(Node $lastNode, array $usePrototypes): array
    {
        $sourceNamespace = null;
        if ($nsDef = $lastNode->getNamespaceDefinition()) {
            if ($nsDef->name instanceof QualifiedName) {
                $sourceNamespace = $nsDef->name->__toString();
            }
        }

        $usePrototypes = array_filter($usePrototypes, function (UseStatement $usePrototype) use ($sourceNamespace) {
            return $sourceNamespace !== $usePrototype->name()->namespace();
        });
        return $usePrototypes;
    }

    private function buildEditText(UseStatement $usePrototype): string
    {
        $editText = [
            'use '
        ];
        if ($usePrototype->type() === UseStatement::TYPE_FUNCTION) {
            $editText[] = 'function ';
        }
        $editText[] = (string) $usePrototype . ';';
        $editText = implode('', $editText);
        return $editText;
    }
}
