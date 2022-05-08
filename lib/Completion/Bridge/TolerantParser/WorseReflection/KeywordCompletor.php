<?php

declare(strict_types=1);

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\TokenStringMaps;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class KeywordCompletor implements TolerantCompletor
{
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (CompletionContext::classClause($node, $offset)) {
            yield from $this->keywords(['implements', 'extends']);
        }

        if (CompletionContext::classMembersBody($node)) {
            yield from $this->keywords(['private', 'protected', 'public']);
        }

        if (CompletionContext::classMembersBody($node->parent)) {
            yield from $this->keywords([
                'function',
                'const',
            ]);
        }

        return true;
    }

    /**
     * @return Generator<Suggestion>
     * @param string[] $keywords
     */
    private function keywords(array $keywords): Generator
    {
        foreach ($keywords as $keyword) {
            yield Suggestion::createWithOptions($keyword, [
                'type' => Suggestion::TYPE_KEYWORD,
            ]);
        }
    }
}
