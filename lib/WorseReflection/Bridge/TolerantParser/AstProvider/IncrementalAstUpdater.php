<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\PhpTokenizer;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Microsoft\PhpParser\TokenStreamProviderInterface;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\AstProvider;
use Throwable;

class IncrementalAstUpdater
{
    public function __construct(
        private SourceFileNode $node,
        private AstProvider $astProvider = new TolerantAstProvider(),
    ) {
    }

    public function apply(TextEdit $edit, TextDocumentUri $uri): IncrementalAstUpdaterResult
    {
        $ast = $this->node;
        $node = $ast->getDescendantNodeAtPosition($edit->start()->toInt());
        $updatedSource = TextEdits::one($edit)->apply($this->node->getFileContents());

        try {
            $reason = $this->doApply($node, $edit);
        } catch (Throwable $e) {
            $reason = $e->getMessage();
        }

        if ($reason === null) {
            $ast->fileContents = $updatedSource;
            return new IncrementalAstUpdaterResult($ast, true);
        }

        return new IncrementalAstUpdaterResult($this->astProvider->get(
            TextDocumentBuilder::create($updatedSource)->uri($uri)->build()
        ), false, $reason);
    }

    private function doApply(Node $node, TextEdit $edit): ?string
    {
        $reason = $this->applyToken($node, $edit);
        if (null === $reason) {
            return null;
        }
        $reason = $this->applyCompoundNode($node, $edit);
        if (null === $reason) {
            return null;
        }
        return $reason;
    }

    private function applyToken(Node $node, TextEdit $edit): ?string
    {
        $token = self::tokenAtRange($node, $edit->range());

        // the text edit is NOT contained in a token
        if (null === $token) {
            return 'text edit not within a token';
        }

        // the range is in the token so align a new edit with the start
        // position of the token text
        $transposedEdit = TextEdit::create(
            $edit->start()->toInt() - $token->getStartPosition(),
            $edit->length(),
            $edit->replacement()
        );

        $originalTokenText = (string)$token->getText($node->getFileContents());
        $editedTokenText = TextEdits::one($transposedEdit)->apply($originalTokenText);
        $tokenizer = $this->tokenizer($editedTokenText);

        $newTokens = [];
        do {
            $newToken = $tokenizer->scanNextToken();

            if ($newToken->kind === TokenKind::EndOfFileToken) {
                break;
            }
            $newTokens[] = $newToken;
        } while (true);

        if (count($newTokens) === 0) {
            return sprintf('no token found (orig: %s, new: %s)', $originalTokenText, $editedTokenText);
        }

        // once we exclude the php start tag and the EOF. If we are left with
        // a single token corresponding to the original then we can update the
        // original. If we have more than one token then we cannot.
        if (count($newTokens) !== 1) {
            return sprintf('more than one token found (%d, orig: %s, new: %s))', count($newTokens), $originalTokenText, $editedTokenText);
        }

        $newToken = $newTokens[0];

        // WHY?
        if (strlen($editedTokenText) !== $newToken->length) {
            return 'edited token text no the same as new token length (???)';
        }

        // if the new token STARTS later than `<?php\n` then it has some
        // leading-whitespace/doc-comment and it will corrupt things.
        if ($newToken->start > 6) {
            return sprintf(
                'new token has leading whitespace or doc comment: %s',
                $editedTokenText
            );
        }

        // if the new token is different from the old one then we need to
        // reparse as for example changing `$foo-` to `$foo->` is the
        // difference between a binary expression ($foo-1) and a member access
        // expression (`$foo->bar`).
        if ($newToken->kind !== $token->kind) {
            return sprintf('new token is not of the same type (orig: %s, new: %s)', $originalTokenText, $editedTokenText);
        }

        $diff = $newToken->length - strlen($originalTokenText);

        // the new token does not include the leading whitespace/docblocks, so add that _back_.
        $token->length = $newToken->length + ($token->start - $token->fullStart);

        foreach ($node->getRoot()->getDescendantTokens() as $rtoken) {
            if ($rtoken->start <= $token->start) {
                continue;
            }
            $rtoken->fullStart += $diff;
            $rtoken->start += $diff;
        }

        return null;
    }

    /**
     * If a token coverts the given offset range, then return it.
     */
    private static function tokenAtRange(Node $node, ByteOffsetRange $range): ?Token
    {
        foreach ($node->getDescendantTokens() as $token) {
            if ($token->getStartPosition() <= $range->start()->toInt() && $token->getEndPosition() >= $range->end()->toInt()) {
                return $token;
            }
        }

        return null;
    }

    private function tokenizer(string $source): TokenStreamProviderInterface
    {
        $tokenizer = (new PhpTokenizer('<?php ' . $source));
        $tokenizer->setCurrentPosition(1);
        return $tokenizer;
    }

    private function applyCompoundNode(Node $node, TextEdit $edit): ?string
    {
        $compoundNode = $node instanceof CompoundStatementNode ? $node : $node->getFirstAncestor(CompoundStatementNode::class);

        if (null === $compoundNode) {
            return 'no compound statement node found';
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
            $this->node->getFileContents(),
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
            return 'did not parse a compound statement node';
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

        return null;
    }
}
