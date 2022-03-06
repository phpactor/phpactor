<?php

namespace Phpactor\Indexer\Adapter\ReferenceFinder;

use Generator;
use Phpactor\Indexer\Adapter\ReferenceFinder\Util\ContainerTypeResolver;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Reflector;

class IndexedImplementationFinder implements ClassImplementationFinder
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var QueryClient
     */
    private $query;

    /**
     * @var ContainerTypeResolver
     */
    private $containerTypeResolver;

    /**
     * @var bool
     */
    private $deepReferences;

    public function __construct(QueryClient $query, Reflector $reflector, bool $deepReferences = true)
    {
        $this->reflector = $reflector;
        $this->query = $query;
        $this->containerTypeResolver = new ContainerTypeResolver($reflector);
        $this->deepReferences = $deepReferences;
    }

    /**
     * @return Locations<Location>
     */
    public function findImplementations(TextDocument $document, ByteOffset $byteOffset): Locations
    {
        $symbolContext = $this->reflector->reflectOffset(
            $document->__toString(),
            $byteOffset->toInt()
        )->symbolContext();

        if ($symbolContext->symbol()->symbolType() === Symbol::METHOD) {
            return $this->methodImplementations($symbolContext);
        }

        $locations = [];
        $implementations = $this->resolveImplementations(FullyQualifiedName::fromString($symbolContext->type()->__toString()));

        foreach ($implementations as $implementation) {
            $record = $this->query->class()->get($implementation);

            $locations[] = new Location(
                TextDocumentUri::fromString($record->filePath()),
                $record->start()
            );
        }

        return new Locations($locations);
    }

    /**
     * @return Locations<Location>
     */
    private function methodImplementations(SymbolContext $symbolContext): Locations
    {
        $container = $symbolContext->containerType();
        $methodName = $symbolContext->symbol()->name();
        $containerType = $this->containerTypeResolver->resolveDeclaringContainerType('method', $methodName, $container);

        if (null === $containerType) {
            return new Locations([]);
        }

        $implementations = $this->resolveImplementations(
            FullyQualifiedName::fromString($containerType),
            true
        );

        $locations = [];

        foreach ($implementations as $implementation) {
            $record = $this->query->class()->get($implementation);
            try {
                $reflection = $this->reflector->reflectClass($implementation->__toString());
                $method = $reflection->methods()->belongingTo($reflection->name())->get($methodName);
            } catch (NotFound $notFound) {
                continue;
            }

            assert($method instanceof ReflectionMethod);
            if ($method->isAbstract()) {
                continue;
            }

            $locations[] = Location::fromPathAndOffset(
                $record->filePath(),
                $method->position()->start()
            );
        }

        return new Locations($locations);
    }

    /**
     * @return Generator<FullyQualifiedName>
     */
    private function resolveImplementations(FullyQualifiedName $type, bool $yieldFirst = false): Generator
    {
        if ($yieldFirst) {
            yield $type;
        }

        foreach ($this->query->class()->implementing($type) as $implementingType) {
            if (false === $this->deepReferences) {
                yield $implementingType;
                continue;
            }

            yield from $this->resolveImplementations($implementingType, true);
        }
    }
}
