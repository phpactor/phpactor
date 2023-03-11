<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\InlayHint;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;

class InlayHintWalker implements Walker
{
    /**
     * @var InlayHint[]
     */
    private array $hints = [];

    public function nodeFqns(): array
    {
        return [];
    }

    public function enter(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        return $frame;
    }

    public function exit(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        if (!$node instanceof CallExpression) {
            return $frame;
        }
        $context = $resolver->resolveNode($frame, $node);
        return $frame;
    }

    /**
     * @return InlayHint[]
     */
    public function hints(): array
    {
        return $this->hints;
    }
}
