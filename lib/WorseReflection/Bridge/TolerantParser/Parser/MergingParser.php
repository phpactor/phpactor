<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Parser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\TolerantAstDiff\AstDiff;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

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
        if ($uri === null) {
            return parent::parseSourceFile($source);
        }



        if (!isset($this->documents[$uri])) {
            $node = parent::parseSourceFile($source, $uri);
            $this->documents[$uri] = $node;
            return $node;
        }

        $node1 = $this->documents[$uri];
        $node2 = parent::parseSourceFile($source, $uri);


        if ($uri === 'file://'.__FILE__) {
        //dump('BEFORE:' . $node1->getFileContents());
            //

            $this->merger->merge($node1,   $node2);

            dump(NodeUtil::dump($node1));
        }

        return $node2;
    }
}
