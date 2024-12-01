<?php

namespace Phpactor\Indexer\Adapter\Tolerant;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\ClassDeclarationIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\ClassLikeReferenceIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\ConstantDeclarationIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\EnumDeclarationIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\FunctionDeclarationIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\FunctionReferenceIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\InterfaceDeclarationIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\MemberIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\TraitDeclarationIndexer;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\TraitUseClauseIndexer;
use Phpactor\Indexer\Model\Exception\CannotIndexNode;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\IndexBuilder;
use Phpactor\TextDocument\TextDocument;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Throwable;

final class TolerantIndexBuilder implements IndexBuilder
{
    private Parser $parser;

    private LoggerInterface $logger;

    /**
     * @param TolerantIndexer[] $indexers
     */
    public function __construct(
        private array $indexers,
        ?LoggerInterface $logger = null,
        ?Parser $parser = null
    ) {
        $this->parser = $parser ?: new Parser();
        $this->logger = $logger ?: new NullLogger();
    }

    public static function create(?LoggerInterface $logger = null): self
    {
        return new self(
            [
                new ClassDeclarationIndexer(),
                new EnumDeclarationIndexer(),
                new FunctionDeclarationIndexer(),
                new InterfaceDeclarationIndexer(),
                new TraitDeclarationIndexer(),
                new TraitUseClauseIndexer(),
                new ClassLikeReferenceIndexer(),
                new FunctionReferenceIndexer(),
                new ConstantDeclarationIndexer(),
                new MemberIndexer(),
            ],
            $logger
        );
    }

    public function index(Index $index, TextDocument $document): void
    {
        foreach ($this->indexers as $indexer) {
            $indexer->beforeParse($index, $document);
        }

        $node = $this->parser->parseSourceFile($document->__toString(), $document->uri()->__toString());
        $this->indexNode($index, $document, $node);
    }

    public function done(Index $index): void
    {
        $index->done();
    }

    private function indexNode(Index $index, TextDocument $document, Node $node): void
    {
        foreach ($this->indexers as $indexer) {
            try {
                if ($indexer->canIndex($node)) {
                    $indexer->index($index, $document, $node);
                }
            } catch (CannotIndexNode $cannotIndexNode) {
                $this->logger->warning(sprintf(
                    'Cannot index node of class "%s" in file "%s": %s',
                    get_class($node),
                    $document->uri()?->__toString() ?? 'unknown',
                    $cannotIndexNode->getMessage()
                ));
            } catch (Throwable $cannotIndexNode) {
                throw new RuntimeException(sprintf(
                    'Could not index document "%s": %s',
                    $document->uri() ?? '',
                    $cannotIndexNode->getMessage()
                ), 0, $cannotIndexNode);
            }
        }

        foreach ($node->getChildNodes() as $childNode) {
            $this->indexNode($index, $document, $childNode);
        }
    }
}
