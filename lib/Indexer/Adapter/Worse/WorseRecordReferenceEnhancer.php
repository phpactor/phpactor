<?php

namespace Phpactor\Indexer\Adapter\Worse;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Indexer\Model\RecordReference;
use Phpactor\Indexer\Model\RecordReferenceEnhancer;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Reflector;
use Psr\Log\LoggerInterface;
use Safe\Exceptions\FilesystemException;
use function Safe\file_get_contents;

class WorseRecordReferenceEnhancer implements RecordReferenceEnhancer
{
    private LoggerInterface $logger;

    private TextDocumentLocator $locator;

    private Reflector $reflector;

    public function __construct(Reflector $reflector, LoggerInterface $logger, TextDocumentLocator $locator)
    {
        $this->logger = $logger;
        $this->locator = $locator;
        $this->reflector = $reflector;
    }

    public function enhance(string $path, ?string $containerType, string $memberName): Generator
    {
        try {
            $document = $this->locator->get(TextDocumentUri::fromString($path));
        } catch (TextDocumentNotFound $error) {
            $this->logger->warning(sprintf(
                'Indexer Reference Finder: Could not read file "%s": %s',
                $path,
                $error->getMessage()
            ));
            return;
        }

        $visitor = new MemberReferenceWalker($document->uri(), $containerType, $memberName);
        $this->reflector->walk($document->__toString(), $visitor);
        foreach ($visitor->locations() as $location) {
            yield $location;
        }
    }
}
