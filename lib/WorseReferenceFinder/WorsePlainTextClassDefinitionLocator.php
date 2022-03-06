<?php

namespace Phpactor\WorseReferenceFinder;

use Exception;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\ReferenceFinder\DefinitionLocation;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\Util\WordAtOffset;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Reflector;

class WorsePlainTextClassDefinitionLocator implements DefinitionLocator
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var array
     */
    private $breakingChars;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Reflector $reflector, array $breakingChars = [])
    {
        $this->reflector = $reflector;
        $this->breakingChars = $breakingChars ?: [
            ' ',
            '"', '\'', '|', '%', '(', ')', '[', ']',':',"\r\n", "\n", "\r"
        ];
        $this->parser = new Parser();
    }

    /**
     * {@inheritDoc}
     */
    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): DefinitionLocation
    {
        $word = $this->extractWord($document, $byteOffset);
        $word = $this->resolveClassName($document, $byteOffset, $word);

        try {
            $reflectionClass = $this->reflector->reflectClassLike($word);
        } catch (NotFound $notFound) {
            throw new CouldNotLocateDefinition(sprintf(
                'Word "%s" could not be resolved to a class',
                $word
            ), 0, $notFound);
        }

        $path = $reflectionClass->sourceCode()->path();

        return new DefinitionLocation(
            TextDocumentUri::fromString($path),
            ByteOffset::fromInt($reflectionClass->position()->start())
        );
    }

    private function extractWord(TextDocument $document, ByteOffset $byteOffset): string
    {
        return (new WordAtOffset(
            WordAtOffset::SPLIT_QUALIFIED_PHP_NAME
        ))->__invoke($document->__toString(), $byteOffset->toInt());
    }

    private function resolveClassName(TextDocument $document, ByteOffset $byteOffset, string $word): string
    {
        if (!$document->language()->isPhp()) {
            return $word;
        }

        $node = $this->parser->parseSourceFile(
            $document->__toString()
        )->getDescendantNodeAtPosition($byteOffset->toInt());

        if ($node instanceof SourceFileNode) {
            $node = $node->getFirstDescendantNode(NamespaceUseClause::class) ?? $node;
        }

        $imports = $this->resolveImportTable($node);

        if (isset($imports[0][$word])) {
            return $imports[0][$word]->__toString();
        }

        if (!isset($word[0])) {
            return $word;
        }

        if ($word[0] !== '\\') {
            $namespace = $this->resolveNamespace($node);
            if ($namespace) {
                return $namespace .'\\'.$word;
            }
        }

        return $word;
    }

    /**
     * Tolerant parser will resolve a docblock comment as the root node, not
     * the node to which the comment belongs. Here we attempt to get the import
     * table from the current node, if that fails then we just do whatever we
     * can to get an import table.
     */
    private function resolveImportTable(Node $node): array
    {
        try {
            return $node->getImportTablesForCurrentScope();
        } catch (Exception $e) {
        }

        foreach ($node->getDescendantNodes() as $node) {
            try {
                $imports = $node->getImportTablesForCurrentScope();
                if (empty($imports[0])) {
                    continue;
                }
                return $imports;
            } catch (Exception $e) {
            }
        }

        return [ [], [], [] ];
    }

    /**
     * As with resolve import table, we try our best.
     */
    private function resolveNamespace(Node $node)
    {
        try {
            return $this->namespaceFromNode($node);
        } catch (Exception $e) {
        }

        foreach ($node->getDescendantNodes() as $node) {
            try {
                return $this->namespaceFromNode($node);
            } catch (Exception $e) {
            }
        }

        return '';
    }

    private function namespaceFromNode(Node $node): string
    {
        if (null === $node->getNamespaceDefinition()) {
            throw new Exception('Locate something with a namespace instead');
        }

        $name = $node->getNamespaceDefinition()->name;

        if (null === $name) {
            return '';
        }
        
        return $name->__toString();
    }
}
