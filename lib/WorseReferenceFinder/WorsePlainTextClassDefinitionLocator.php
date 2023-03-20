<?php

namespace Phpactor\WorseReferenceFinder;

use Exception;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\Util\WordAtOffset;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class WorsePlainTextClassDefinitionLocator implements DefinitionLocator
{
    private Parser $parser;

    public function __construct(private Reflector $reflector)
    {
        $this->parser = new Parser();
    }


    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): TypeLocations
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

        return new TypeLocations([
            new TypeLocation(
                $reflectionClass->type(),
                new Location(
                    TextDocumentUri::fromString($path),
                    ByteOffset::fromInt($reflectionClass->position()->startAsInt())
                )
            )
        ]);
    }

    private function extractWord(TextDocument $document, ByteOffset $byteOffset): string
    {
        $offset = $byteOffset->toInt() + 1;
        $docLength = strlen($document->__toString());
        if ($offset > $docLength) {
            $offset = $docLength;
        }
        return (new WordAtOffset(
            WordAtOffset::SPLIT_QUALIFIED_PHP_NAME
        ))->__invoke($document->__toString(), $offset);
    }

    private function resolveClassName(TextDocument $document, ByteOffset $byteOffset, string $word): string
    {
        if (!$document->language()->isPhp()) {
            return $word;
        }

        $node = $this->parser->parseSourceFile($document->__toString());
        $node = NodeUtil::firstDescendantNodeAfterOffset($node, $byteOffset->toInt());

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
        } catch (Exception) {
        }

        foreach ($node->getDescendantNodes() as $node) {
            try {
                $imports = $node->getImportTablesForCurrentScope();
                if (empty($imports[0])) {
                    continue;
                }
                return $imports;
            } catch (Exception) {
            }
        }

        return [ [], [], [] ];
    }

    /**
     * As with resolve import table, we try our best.
     */
    private function resolveNamespace(Node $node): string
    {
        try {
            return $this->namespaceFromNode($node);
        } catch (Exception) {
        }

        foreach ($node->getDescendantNodes() as $node) {
            try {
                return $this->namespaceFromNode($node);
            } catch (Exception) {
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

        if (!$name instanceof QualifiedName) {
            return '';
        }

        return $name->__toString();
    }
}
