<?php

namespace Phpactor\Indexer\Adapter\ReferenceFinder;

use Generator;
use Phpactor\Indexer\Adapter\ReferenceFinder\Util\ContainerTypeResolver;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\LocationConfidence;
use Phpactor\Indexer\Model\RecordReference;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Reflector;
use RuntimeException;

class IndexedReferenceFinder implements ReferenceFinder
{
    private ContainerTypeResolver $containerTypeResolver;

    public function __construct(
        private QueryClient $query,
        private Reflector $reflector,
        ?ContainerTypeResolver $containerTypeResolver = null,
        private bool $deepReferences = true
    ) {
        $this->containerTypeResolver = $containerTypeResolver ?: new ContainerTypeResolver($reflector);
    }

    /**
     * @return Generator<PotentialLocation>
     */
    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Generator
    {
        try {
            $nodeContext = $this->reflector->reflectOffset(
                $document,
                $byteOffset->toInt()
            )->nodeContext();
        } catch (NotFound) {
            return;
        }

        foreach ($this->resolveReferences($nodeContext) as $locationConfidence) {
            if ($locationConfidence->isSurely()) {
                yield PotentialLocation::surely($locationConfidence->location());
                continue;
            }

            if ($locationConfidence->isMaybe()) {
                yield PotentialLocation::maybe($locationConfidence->location());
                continue;
            }

            yield PotentialLocation::not($locationConfidence->location());
        }
    }

    /**
     * @return Generator<LocationConfidence>
     */
    private function resolveReferences(NodeContext $nodeContext): Generator
    {
        $symbolType = $nodeContext->symbol()->symbolType();
        if ($symbolType === Symbol::CLASS_) {
            foreach ($this->implementationsOf($nodeContext->type()->__toString()) as $implementationFqn) {
                yield from $this->query->class()->referencesTo($implementationFqn);
            }
            return;
        }

        if ($symbolType === Symbol::FUNCTION) {
            yield from $this->query->function()->referencesTo($nodeContext->symbol()->name());
            return;
        }

        $memberType = $nodeContext->symbol()->symbolType();
        if (in_array($memberType, [
            Symbol::METHOD,
            Symbol::CONSTANT,
            Symbol::PROPERTY,
            Symbol::VARIABLE,
            Symbol::CASE,
        ])) {
            $containerType = $this->containerTypeResolver->resolveDeclaringClass(
                $this->symbolTypeToMemberType($nodeContext),
                $nodeContext->symbol()->name(),
                $nodeContext->containerType()
            );

            if (null === $containerType) {
                yield from $this->query->member()->referencesTo(
                    $this->symbolTypeToReferenceType($nodeContext),
                    $nodeContext->symbol()->name(),
                    null
                );
                return;
            }

            // note that we check the all implementations: this will multiply
            // the number of NOT and MAYBE matches
            foreach ($this->implementationsOf($containerType) as $implementations) {
                yield from $this->memberReferencesTo(
                    $this->symbolTypeToReferenceType($nodeContext),
                    $nodeContext->symbol()->name(),
                    $implementations
                );
            }
            return;
        }
    }

    /**
     * @return Generator<string>
     */
    private function implementationsOf(string $fqn): Generator
    {
        yield $fqn;

        if (false === $this->deepReferences) {
            return;
        }

        foreach ($this->query->class()->implementing($fqn) as $implementation) {
            yield $implementation->__toString();
        }
    }

    /**
     * @return ReflectionMember::TYPE_*
     */
    private function symbolTypeToMemberType(NodeContext $nodeContext): string
    {
        $symbolType = $nodeContext->symbol()->symbolType();

        return match ($symbolType) {
            Symbol::CASE => ReflectionMember::TYPE_CASE,
            Symbol::METHOD => ReflectionMember::TYPE_METHOD,
            Symbol::PROPERTY => ReflectionMember::TYPE_PROPERTY,
            Symbol::VARIABLE => ReflectionMember::TYPE_PROPERTY,
            Symbol::CONSTANT => ReflectionMember::TYPE_CONSTANT,

            default => throw new RuntimeException(sprintf(
                'Could not convert symbol type "%s" to member type',
                $symbolType
            ))
        };
    }

    /**
     * @return MemberRecord::TYPE_*
     */
    private function symbolTypeToReferenceType(NodeContext $nodeContext): string
    {
        $symbolType = $nodeContext->symbol()->symbolType();

        return match ($symbolType) {
            Symbol::CASE => MemberRecord::TYPE_CONSTANT,
            Symbol::METHOD => MemberRecord::TYPE_METHOD,
            Symbol::PROPERTY => MemberRecord::TYPE_PROPERTY,
            Symbol::VARIABLE => MemberRecord::TYPE_PROPERTY,
            Symbol::CONSTANT => MemberRecord::TYPE_CONSTANT,

            default => throw new RuntimeException(sprintf(
                'Could not convert symbol type "%s" to reference type',
                $symbolType
            ))
        };
    }

    /**
     * @param "method"|"constant"|"property" $referenceType
     * @return Generator<LocationConfidence>
     */
    private function memberReferencesTo(string $referenceType, string $memberName, string $containerType): Generator
    {
        if ($memberName === '__construct' && $referenceType === 'method') {
            yield from $this->newObjectReferences($containerType);
            return;
        }
        yield from $this->query->member()->referencesTo($referenceType, $memberName, $containerType);
    }
    /**
     * @return Generator<LocationConfidence>
     */
    private function newObjectReferences(string $containerType): Generator
    {
        /** @var ?ClassRecord $class */
        $class = $this->query->class()->get($containerType);
        if (!$class) {
            return;
        }

        foreach ($class->references() as $reference) {
            /** @var ?FileRecord $file */
            $file = $this->query->file()->get($reference);
            if (null === $file) {
                continue;
            }
            foreach ($file->references() as $fileReference) {
                if (
                    $fileReference->type() !== 'class' ||
                    !$fileReference->hasFlag(RecordReference::FLAG_NEW_OBJECT) ||
                    $fileReference->identifier() !== $containerType
                ) {
                    continue;
                }
                yield LocationConfidence::surely(
                    Location::fromPathAndOffsets(
                        $file->filePath() ?? '',
                        $fileReference->start(),
                        $fileReference->end()
                    )
                );
            }
        }
    }
}
