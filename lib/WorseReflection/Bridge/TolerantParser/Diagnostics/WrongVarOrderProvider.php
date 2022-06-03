<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\DocblockParser\Ast\Tag\InvertedVarTag;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class WrongVarOrderProvider implements DiagnosticProvider
{
    private DocblockParser $docblockParser;

    public function __construct(DocblockParser $docblockParser = null)
    {
        $this->docblockParser = $docblockParser ?: DocblockParser::create();
    }

    public function provide(NodeContextResolver $resolver, Frame $frame, Node $node): Generator
    {
        $comment = $node->getDocCommentText();

        if (!$comment) {
            return;
        }

        $parsedComment = $this->docblockParser->parse($node->getLeadingCommentAndWhitespaceText());
        $invertedVarTags = $parsedComment->descendantElements(InvertedVarTag::class);

        $offset = $node->getFullStartPosition();

        foreach ($invertedVarTags as $invertedVarTag) {
            $start = $offset + $invertedVarTag->start();
            $end = $offset + $invertedVarTag->end();

            yield new WrongVarOrderDiagnostic(ByteOffsetRange::fromInts($start, $end), '@var type must come before element name');
        }
    }
}
