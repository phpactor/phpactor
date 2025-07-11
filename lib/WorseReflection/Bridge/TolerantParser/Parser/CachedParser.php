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
    private Cache $cache;

    public function __construct(?Cache $cache = null, private ?CacheForDocument $cacheForDocuments = null)
    {
        $this->cache = $cache ?: new TtlCache();

        parent::__construct();

    }

    public function parseSourceFile(string $source, ?string $uri = null): SourceFileNode
    {
        $key = 'parser.' . md5($source);

        if ($this->cacheForDocuments !== null && $uri !== null) {
            return $this->cacheForDocuments->getOrSet(TextDocumentUri::fromString($uri), $key, function () use ($source, $uri) {
                return parent::parseSourceFile($source, $uri);
            });
        }
        return $this->cache->getOrSet($key, function () use ($source, $uri) {
            return parent::parseSourceFile($source, $uri);
        });
    }
}
