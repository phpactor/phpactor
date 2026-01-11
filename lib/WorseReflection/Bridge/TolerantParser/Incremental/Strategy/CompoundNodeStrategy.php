<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\Strategy;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Parser;
use Phpactor\TextDocument\TextEdits;
use Phpactor\TextDocument\TextEdit;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\OperationResult;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\UpdaterStrategy;

class CompoundNodeStrategy implements UpdaterStrategy
{
    public function apply(Node $node, TextEdit $edit): OperationResult
    {
        $result = new OperationResult('compound node');
        $compoundNode = $node instanceof CompoundStatementNode ? $node : $node->getFirstAncestor(CompoundStatementNode::class);

        if (null === $compoundNode) {
            return $result->fail('no compound statement node found');
        }

        assert($compoundNode instanceof CompoundStatementNode);

        $extractStartPosition = null;

        // determine from where we want to extract the text to re-parse.
        // this should be from the statement being edited or the position after
        // the closing brace of the compound statement
        $statementNb = 0;
        foreach ($compoundNode->statements as $statementCandidate) {
            // if the text edit is _contained_ in a statement
            if ($statementCandidate->getFullStartPosition() < $edit->start()->toInt() && $statementCandidate->getEndPosition() > $edit->end()->toInt()) {
                $extractStartPosition = $statementCandidate->getFullStartPosition();
                break;
            }
            $statementNb++;
        }

        if (null === $extractStartPosition) {
            $extractStartPosition = $compoundNode->openBrace->getStartPosition() + 1;
            $statementNb = 0;
        }

        // extract the remaining textual content of the node
        $textToEdit = substr(
            $node->getFileContents(),
            $extractStartPosition,
            $compoundNode->getEndPosition() - $extractStartPosition
        );

        // transpose the text edit to the textToEdit
        $transposedEdit = TextEdit::create(
            $edit->start()->toInt() - $extractStartPosition,
            $edit->length(),
            $edit->replacement()
        );

        // apply the edit
        $extractText = TextEdits::one($transposedEdit)->apply($textToEdit);

        // parse the extract, prepending the starting brace to make it a compound node
        $newCompoundNode = (new Parser())->parseSourceFile('<?php {' .$extractText)->statementList[1];

        if (!$newCompoundNode instanceof CompoundStatementNode) {
            return $result->fail('did not parse a compound statement node');
        }

        // align the tokens in the compound node
        $diff = $extractStartPosition;
        $lastToken = null;
        foreach ($newCompoundNode->statements as $statement) {
            foreach ($statement->getDescendantTokens() as $rtoken) {
                $rtoken->fullStart += -7 + $diff;
                $rtoken->start += -7 + $diff;
                $lastToken = $rtoken;
            }
        }

        // graft the node onto the original node
        foreach ($newCompoundNode->statements as $rstatement) {
            $rstatement->parent = $compoundNode;
        }
        $compoundNode->statements = array_merge(
            array_slice($compoundNode->statements, 0, $statementNb),
            $newCompoundNode->statements,
        );
        $diff = strlen($extractText) - strlen($textToEdit);

        // align the remaining tokens
        $found = false;
        foreach ($node->getRoot()->getDescendantTokens() as $rtoken) {
            if ($found === false && $rtoken === $lastToken) {
                $found = true;
                continue;
            }
            if (!$found) {
                continue;
            }
            $rtoken->fullStart += $diff;
            $rtoken->start += $diff;
        }

        return $result;
    }
}
