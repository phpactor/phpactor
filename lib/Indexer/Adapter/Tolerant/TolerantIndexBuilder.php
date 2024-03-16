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

final class TolerantIndexBuilder implements IndexBuilder
{
    private Parser $parser;

    /**
     * @param TolerantIndexer[] $indexers
     */
    public function __construct(
        private Index $index,
        private array $indexers,
        private LoggerInterface $logger,
        ?Parser $parser = null
    ) {
        $this->parser = $parser ?: new Parser();
    }

    public static function create(Index $index, ?LoggerInterface $logger = null): self
    {
        return new self(
            $index,
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
            $logger ?: new NullLogger()
        );
    }

    public function index(TextDocument $document): void
    {
        foreach ($this->indexers as $indexer) {
            $indexer->beforeParse($this->index, $document);
        }

        $node = $this->parser->parseSourceFile($document->__toString(), $document->uri()->__toString());
        $this->indexNode($document, $node);
    }

    public function done(): void
    {
        $this->index->done();
    }

    private function indexNode(TextDocument $document, Node $node): void
    {
        foreach ($this->indexers as $indexer) {
            try {
                if ($indexer->canIndex($node)) {
                    $indexer->index($this->index, $document, $node);
                }
            } catch (CannotIndexNode $cannotIndexNode) {
                $this->logger->warning(sprintf(
                    'Cannot index node of class "%s" in file "%s": %s',
                    get_class($node),
                    $document->uri()?->__toString() ?? 'unknown',
                    $cannotIndexNode->getMessage()
                ));
            }
        }

        foreach ($node->getChildNodes() as $childNode) {
            $this->indexNode($document, $childNode);
        }
    }
}
