<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Parser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\TolerantAstDiff\AstDiff;
use Phpactor\WorseReflection\Core\CacheForDocument;

class MergingParser extends Parser
{
    /**
     * @var array<string,SourceFileNode>
     */
    private $documents = [];

    public function __construct(
        private AstDiff $merger
    ) {
        parent::__construct();
    }

    public function parseSourceFile(string $source, ?string $uri = null): SourceFileNode
    {
        if (null === $uri) {
            return parent::parseSourceFile($source);
        }
        if (!isset($this->documents[$uri])) {
            $node = parent::parseSourceFile($source);
            dump(sprintf('ini: %s (%s)', $node->getFullWidth(), strlen($node->getText())));
            $this->documents[$uri] = $node;
            return $node;
        }

        $node1 = $this->documents[$uri];
        $node2 = parent::parseSourceFile($source);

        dump(sprintf('new: %s (%s)', $node2->getFullWidth(), strlen($node2->getText())));
        $this->merger->merge($node1, $node2);
        dump(sprintf('upd: %s (%s)', $node1->getFullWidth(), strlen($node1->getText())));
        return $node1;
    }
}
