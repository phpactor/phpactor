<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Parser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\TextDocument\TextDocumentUri;
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

    public function get(string|TextDocument $document, ?string $uri = null): Node
    {
        if (is_string($document) && null === $uri) {
            return $this->cache->getOrSet(
                '__parser__' . md5($document),
                function () use ($document) {
                    return $this->parser->parseSourceFile($document);
                }
            );
        }

        if (null !== $uri && $document instanceof TextDocument) {
            throw new \RuntimeException(
                'Cannot provide a URI when using a TextDocument'
            );
        }

        if (null !== $uri) {
            $document = TextDocumentBuilder::create($document)->uri($uri)->build();
        }

        return $this->cacheForDocument->getOrSet(
            $document->uri(),
            'ast',
            function () use ($document) {
                return $this->parser->parseSourceFile($document->__toString(), $document->uri()->__toString());
            }
        );
    }
}
