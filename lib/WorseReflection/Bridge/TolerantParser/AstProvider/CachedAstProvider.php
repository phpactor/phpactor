<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider;

use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Phpactor\WorseReflection\Core\Cache\TtlCache;

class CachedAstProvider implements AstProvider
{
    private CacheForDocument $cacheForDocument;

    public function __construct(
        private AstProvider $astProvider,
        private Cache $cache = new TtlCache(),
        ?CacheForDocument $cacheForDocument = null
    ) {
        $this->cacheForDocument = $cacheForDocument ?? CacheForDocument::none();
    }

    public function get(TextDocument $document): SourceFileNode
    {
        if ($document->uri() === null) {
            return $this->cache->getOrSet(
                'astanon:' . md5($document),
                function () use ($document) {
                    return $this->astProvider->get($document);
                }
            );
        }

        return $this->cacheForDocument->getOrSet(
            $document->uri(),
            'ast',
            function () use ($document) {
                return $this->astProvider->get($document);
            }
        );
    }
}
