<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Completor\NameSearcherCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\Util\OffsetHelper;
use Phpactor\Name\NameUtil;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class DoctrineAnnotationCompletor extends NameSearcherCompletor implements Completor
{
    private Parser $parser;

    public function __construct(
        NameSearcher $nameSearcher,
        private Reflector $reflector,
        ?Parser $parser = null
    ) {
        parent::__construct($nameSearcher, null);
        $this->parser = $parser ?: new Parser();
    }

    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $truncatedSource = $this->truncateSource((string) $source, $byteOffset->toInt());
        $sourceNodeFile = $this->parser->parseSourceFile((string) $source, $source->uri());

        $node = $this->findNodeForPhpdocAtPosition(
            $sourceNodeFile,
            // the parser requires the byte offset, not the char offset
            strlen($truncatedSource)
        );

        if (!$node) {
            // Ignore this case is the cursor is not in a phpdoc block
            return true;
        }

        if (!$annotation = $this->extractAnnotation($truncatedSource)) {
            // Ignore if not an annotation
            return true;
        }

        $namespace = NodeUtil::namespace($node);
        if (NameUtil::isQualified($annotation) && $namespace) {
            $annotation = '\\' . NameUtil::join($namespace, $annotation);
        }

        $suggestions = $this->completeName($annotation, $source->uri());

        foreach ($suggestions as $suggestion) {
            if (!$this->isAnAnnotation($suggestion)) {
                continue;
            }

            yield $suggestion;
        }

        return $suggestions->getReturn();
    }

    protected function createSuggestionOptions(
        NameSearchResult $result,
        ?TextDocumentUri $sourceUri = null,
        ?Node $node = null,
        bool $wasAbsolute = false
    ): array {
        return array_merge(parent::createSuggestionOptions($result, null, $node, $wasAbsolute), [
            'snippet' => (string) $result->name()->head() .'($1)$0',
        ]);
    }

    private function truncateSource(string $source, int $byteOffset): string
    {
        // truncate source at byte offset - we don't want the rest of the source
        // file contaminating the completion (for example `$foo($<>\n    $bar =
        // ` will evaluate the Variable node as an expression node with a
        // double variable `$\n    $bar = `
        $truncatedSource = substr($source, 0, $byteOffset);

        // determine the last non-whitespace _character_ offset
        $characterOffset = OffsetHelper::lastNonWhitespaceCharacterOffset($truncatedSource);

        // truncate the source at the character offset
        $truncatedSource = mb_substr($source, 0, $characterOffset);

        return $truncatedSource;
    }

    private function findNodeForPhpdocAtPosition(SourceFileNode $sourceNodeFile, int $position): ?Node
    {
        /** @var Node $node */
        foreach ($sourceNodeFile->getDescendantNodes() as $node) {
            if (
                $node->getFullStartPosition() < $position
                && $position < $node->getStartPosition()
            ) {
                // If the text is a phpdoc block return the node
                return $node->getDocCommentText() ? $node : null;
            }
        }

        return null;
    }

    private function isAnAnnotation(Suggestion $suggestion): bool
    {
        if (null === $suggestion->nameImport()) {
            return false;
        }

        try {
            $reflectionClass = $this->reflector->reflectClass($suggestion->nameImport());
            $docblock = $reflectionClass->docblock();

            return str_contains($docblock->raw(), '@Annotation');
        } catch (NotFound) {
            return false;
        }
    }

    private function extractAnnotation(string $truncatedSource): ?string
    {
        $count = 0;
        $annotation = preg_replace('/.*@([^\\@\s\t*]+)$/s', '$1', $truncatedSource, 1, $count);

        if (0 === $count) {
            return null;
        }

        return $annotation;
    }
}
