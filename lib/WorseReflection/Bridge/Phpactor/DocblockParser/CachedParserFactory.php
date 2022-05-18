<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\TypeResolver;

class CachedParserFactory implements DocBlockFactory
{
    private Cache $cache;

    private DocBlockFactory $innerFactory;

    public function __construct(DocBlockFactory $innerFactory, Cache $cache)
    {
        $this->cache = $cache;
        $this->innerFactory = $innerFactory;
    }

    public function create(TypeResolver $resolver, string $docblock): DocBlock
    {
        return $this->cache->getOrSet($docblock, function () use ($resolver, $docblock) {
            return $this->innerFactory->create($resolver, $docblock);
        });
    }
}
