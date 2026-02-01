<?php

namespace Phpactor\WorseReflection\Core\AstProvider;

use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Core\CacheForDocument;
use RuntimeException;

class CachedAstProvider implements AstProvider
{
    private const AST_HASH_KEY = 'ast.hash_key';
    private const AST_KEY = 'ast.key';

    private CacheForDocument $cacheForDocument;

    public function __construct(
        private AstProvider $astProvider,
        ?CacheForDocument $cacheForDocument = null
    ) {
        $this->cacheForDocument = $cacheForDocument ?? CacheForDocument::none();
    }

    public function get(TextDocument $document): SourceFileNode
    {
        $cache = $this->cacheForDocument->cacheForDocument(
            $document->uri() ?? TextDocumentUri::fromString('untitled:///document'),
        );
        $hashEntry = $cache->get(self::AST_HASH_KEY);

        $documentHash = md5($document->__toString());

        if (null === $hashEntry || $documentHash !== $hashEntry->string()) {
            $ast = $this->astProvider->get($document);
            $cache->set(self::AST_KEY, $ast);
            $cache->set(self::AST_HASH_KEY, $documentHash);
            return $ast;
        }

        return $cache->get(
            self::AST_KEY
        )?->expect(
            SourceFileNode::class
        ) ?? throw new RuntimeException('AST Cache cannot be null');
    }
}
