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
            $this->documents[$uri] = $node;
            return $node;
        }

        $node1 = $this->documents[$uri];
        $node2 = parent::parseSourceFile($source);

        $this->merger->merge($node1, $node2);
        return $node1;
    }
}
