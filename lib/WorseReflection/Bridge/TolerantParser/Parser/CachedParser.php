<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Parser;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Phpactor\WorseReflection\Core\Cache\TtlCache;

class CachedParser extends Parser
{
    private CacheForDocument $cacheForDocument;

    public function __construct(private Cache $cache = new TtlCache(), ?CacheForDocument $cacheForDocument = null)
    {
        parent::__construct();
        $this->cacheForDocument = $cacheForDocument ?? CacheForDocument::none();
    }

    public function parseSourceFile(string $source, ?string $uri = null): SourceFileNode
    {
        if ($uri === null) {
            return $this->cache->getOrSet(
                '__parser__' . md5($source),
                function () use ($source, $uri) {
                    return parent::parseSourceFile($source, $uri);
                }
            );
        }

        return $this->cacheForDocument->getOrSet(
            TextDocumentUri::fromString($uri),
            'ast',
            function () use ($source, $uri) {
                return parent::parseSourceFile($source, $uri);
            }
        );
    }
}
