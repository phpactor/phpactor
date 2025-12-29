<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider;

use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class IncrementalAstProvider implements AstProvider
{
    const PREVIOUS_AST = 'last_ast';

    public function __construct(
        private AstProvider $provider,
        private CacheForDocument $cache,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function close(TextDocumentUri $uri): void
    {
        $cache = $this->cache->cacheForDocument($uri);
        $cache->remove(self::PREVIOUS_AST);
    }

    public function open(TextDocument $document): void
    {
        $cache = $this->cache->cacheForDocument($document->uriOrThrow());
        $ast = $this->provider->get($document);
        $cache->set(self::PREVIOUS_AST, $ast);
    }

    public function get(TextDocument $document): SourceFileNode
    {
        $cache = $this->cache->cacheForDocument(
            $document->uri() ?? TextDocumentUri::fromString('untitled:///document')
        );

        $entry = $cache->get(self::PREVIOUS_AST);

        if (null === $entry) {
            $ast = $this->provider->get($document);
            $cache->set(self::PREVIOUS_AST, $ast);
            return $ast;
        }

        $ast = $entry->expect(SourceFileNode::class);

        return $ast;
    }

    /**
     * @param TextEdit[] $edits
     */
    public function update(TextDocumentUri $uri, array $edits): SourceFileNode
    {
        $cache = $this->cache->cacheForDocument($uri);
        $entry = $cache->get(self::PREVIOUS_AST);

        if (null === $entry) {
            throw new RuntimeException(sprintf(
                'Document has not been opened: %s',
                $uri->__toString()
            ));
        }

        $ast = $entry->expect(SourceFileNode::class);
        $content = $ast->fileContents;

        $start = microtime(true);
        foreach ($edits as $edit) {

            $astResult = (new IncrementalAstUpdater($ast, $this->provider))->apply($edit, $uri);

            if (false === $astResult->success) {
                $this->logger->warning(sprintf('PARS fell back to full parse: %s', $astResult->failureReason));
            }

            $ast = $astResult->ast;
        }
        $this->logger->info(sprintf(
            'PARS %s (incremental %d edits)',
            number_format(microtime(true) - $start, 4),
            count($edits),
        ));

        $cache->set(self::PREVIOUS_AST, $ast);

        return $ast;
    }
}
