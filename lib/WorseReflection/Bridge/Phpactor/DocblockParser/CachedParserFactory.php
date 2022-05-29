<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;

class CachedParserFactory implements DocBlockFactory
{
    private Cache $cache;

    private DocBlockFactory $innerFactory;

    public function __construct(DocBlockFactory $innerFactory, Cache $cache)
    {
        $this->cache = $cache;
        $this->innerFactory = $innerFactory;
    }

    public function create(string $docblock): DocBlock
    {
        if (!trim($docblock)) {
            return new PlainDocblock('');
        }

        return $this->cache->getOrSet('docblock_' . $docblock, function () use ($docblock) {
            return $this->innerFactory->create($docblock);
        });
    }
}
