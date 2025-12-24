<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Parser;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Phpactor\WorseReflection\Core\Cache\TtlCache;

class CachedParser implements AstProvider
{
    private CacheForDocument $cacheForDocument;

    private Parser $parser;

    public function __construct(
        private Cache $cache = new TtlCache(),
        ?CacheForDocument $cacheForDocument = null
    ) {
        $this->cacheForDocument = $cacheForDocument ?? CacheForDocument::none();
        $this->parser = new Parser();
    }

    public function get(string|TextDocument $document, ?string $uri = null): SourceFileNode
    {

        $uri = ($document instanceof TextDocument ? $document->uri()?->__toString() : null) ?? $uri;

        if (null === $uri) {
            return $this->cache->getOrSet(
                '__parser__' . md5($document),
                function () use ($document) {
                    return $this->parser->parseSourceFile((string)$document);
                }
            );
        }

        return $this->cacheForDocument->getOrSet(
            TextDocumentUri::fromString($uri),
            'ast',
            function () use ($document, $uri) {
                return $this->parser->parseSourceFile((string)$document, $uri);
            }
        );
    }
}
