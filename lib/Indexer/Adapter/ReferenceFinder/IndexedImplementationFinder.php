<?php

namespace Phpactor\Indexer\Adapter\ReferenceFinder;

use Generator;
use Phpactor\Indexer\Adapter\ReferenceFinder\Util\ContainerTypeResolver;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\Record\HasPath;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Reflector;

class IndexedImplementationFinder implements ClassImplementationFinder
{
    private ContainerTypeResolver $containerTypeResolver;

    public function __construct(
        private QueryClient $query,
        private Reflector $reflector,
        private bool $deepReferences = true
    ) {
        $this->containerTypeResolver = new ContainerTypeResolver($reflector);
    }

    /**
     * @return Locations<Location>
     */
    public function findImplementations(TextDocument $document, ByteOffset $byteOffset, bool $includeDefinition = false): Locations
    {
        $nodeContext = $this->reflector->reflectOffset(
            $document->__toString(),
            $byteOffset->toInt()
        )->nodeContext();

        $symbolType = $nodeContext->symbol()->symbolType();

        if (
            $symbolType === Symbol::METHOD ||
            $symbolType === Symbol::CONSTANT ||
            $symbolType === Symbol::CASE ||
            $symbolType === Symbol::VARIABLE ||
            $symbolType === Symbol::PROPERTY
        ) {
            if ($symbolType === Symbol::CASE) {
                $symbolType = 'enum';
            }
            if ($symbolType === Symbol::VARIABLE) {
                $symbolType = Symbol::PROPERTY;
            }
            return $this->memberImplementations($nodeContext, $symbolType, $includeDefinition);
        }

        $locations = [];
        $implementations = $this->resolveImplementations(FullyQualifiedName::fromString($nodeContext->type()->__toString()));

        foreach ($implementations as $implementation) {
            $record = $this->query->class()->get($implementation);

            if (null === $record) {
                continue;
            }

            $locations[] = new Location(
                TextDocumentUri::fromString($record->filePath()),
                $record->start()
            );
        }

        return new Locations($locations);
    }

    /**
     * @return Locations<Location>
     * @param ReflectionMember::TYPE_* $symbolType
     */
    private function memberImplementations(NodeContext $nodeContext, string $symbolType, bool $includeDefinition): Locations
    {
        $container = $nodeContext->containerType();
        $methodName = $nodeContext->symbol()->name();
        $containerType = $this->containerTypeResolver->resolveDeclaringContainerType($symbolType, $methodName, $container);

        if (!$containerType) {
            return new Locations([]);
        }

        $implementations = $this->resolveImplementations(
            FullyQualifiedName::fromString($containerType),
            true
        );

        $locations = [];

        foreach ($implementations as $implementation) {
            $record = $this->query->class()->get($implementation);

            if (null === $record) {
                continue;
            }

            try {
                $reflection = $this->reflector->reflectClassLike($implementation->__toString());
                $member = $reflection->members()->byMemberType($symbolType)->belongingTo($reflection->name())->get($methodName);
            } catch (NotFound) {
                continue;
            }

            if (false === $includeDefinition) {
                if (!$reflection instanceof ReflectionClass) {
                    continue;
                }

                if ($member instanceof ReflectionMethod) {
                    if ($member->isAbstract()) {
                        continue;
                    }
                }
            }

            if (!$record instanceof HasPath) {
                continue;
            }

            $path = $record->filePath();

            if (null === $path) {
                continue;
            }

            $locations[] = Location::fromPathAndOffset(
                $path,
                $member->position()->start()
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
