<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\Strategy;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\PhpTokenizer;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Microsoft\PhpParser\TokenStreamProviderInterface;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\OperationResult;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\UpdaterStrategy;

class TokenStrategy implements UpdaterStrategy
{
    public function apply(Node $node, TextEdit $edit): OperationResult
    {
        $operationResult = new OperationResult('token');

        $token = self::tokenAtRange($node, $edit->range());

        // the text edit is NOT contained in a token
        if (null === $token) {
            return $operationResult->fail('text edit not within a token');
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
            return $operationResult->fail(sprintf(
                'no token found (orig: %s, new: %s)',
                $originalTokenText,
                $editedTokenText
            ));
        }

        // once we exclude the php start tag and the EOF. If we are left with
        // a single token corresponding to the original then we can update the
        // original. If we have more than one token then we cannot.
        if (count($newTokens) !== 1) {
            return $operationResult->fail(sprintf(
                'more than one token found (%d, orig: %s, new: %s))',
                count($newTokens),
                $originalTokenText,
                $editedTokenText
            ));
        }

        $newToken = $newTokens[0];

        // WHY?
        if (strlen($editedTokenText) !== $newToken->length) {
            return $operationResult->fail(
                'edited token text no the same as new token length (???)'
            );
        }

        // if the new token STARTS later than `<?php\n` then it has some
        // leading-whitespace/doc-comment and it will corrupt things.
        if ($newToken->start > 6) {
            return $operationResult->fail(sprintf(
                'new token has leading whitespace or doc comment: %s',
                $editedTokenText
            ));
        }

        // if the new token is different from the old one then we need to
        // reparse as for example changing `$foo-` to `$foo->` is the
        // difference between a binary expression ($foo-1) and a member access
        // expression (`$foo->bar`).
        if ($newToken->kind !== $token->kind) {
            return $operationResult->fail(sprintf(
                'new token is not of the same type (orig: %s, new: %s)',
                $originalTokenText,
                $editedTokenText
            ));
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

        return $operationResult;
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

}
