<?php

namespace Phpactor\Extension\Symfony\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Range;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Symfony\Component\Console\Completion\Suggestion;

class SymfonyContainerCompletor implements TolerantCompletor
{
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!$node instanceof CallExpression) {
            return;
        }

        $memberAccess = $node->callableExpression;

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $expression = $memberAccess->dereferencableExpression;


        yield Suggestion::createWithOptions($suggestion, [
            'label' => $step->pattern(),
            'short_description' => $step->context()->class(),
            'type' => Suggestion::TYPE_SNIPPET,
            'range' => Range::fromStartAndEnd(
                $startOffset,
                $startOffset + strlen($partial)
            )
        ]);
    }
}
