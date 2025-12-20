<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Parser;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\Cache\TtlCache;

class CachedParser extends Parser
{
    public function __construct(private Cache $cache = new TtlCache())
    {
        parent::__construct();
    }

    public function parseSourceFile(string $source, ?string $uri = null): SourceFileNode
    {
        return $this->cache->getOrSet('__parser__' . md5($source), function () use ($source, $uri) {
            return parent::parseSourceFile($source, $uri);
        });
    }
}
