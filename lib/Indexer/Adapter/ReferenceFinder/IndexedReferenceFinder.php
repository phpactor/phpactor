<?php

namespace Phpactor\Indexer\Adapter\ReferenceFinder;

use Generator;
use Phpactor\Indexer\Adapter\ReferenceFinder\Util\ContainerTypeResolver;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\LocationConfidence;
use Phpactor\Indexer\Model\RecordReference;
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
                yield PotentialLocation::surely($locationConfidence->range());
                continue;
            }

            if ($locationConfidence->isMaybe()) {
                yield PotentialLocation::maybe($locationConfidence->range());
                continue;
            }

            yield PotentialLocation::not($locationConfidence->range());
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
            $containerType = $this->containerTypeResolver->resolveDeclaringContainerType(
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
            foreach ($this->implementationsOf($containerType) as $containerType) {
                yield from $this->memberReferencesTo(
                    $this->symbolTypeToReferenceType($nodeContext),
                    $nodeContext->symbol()->name(),
                    $containerType
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
            yield from $this->implementationsOf($implementation->__toString());
        }
    }

    /**
     * @return ReflectionMember::TYPE_*
     */
    private function symbolTypeToMemberType(NodeContext $nodeContext): string
    {
        $symbolType = $nodeContext->symbol()->symbolType();

        if ($symbolType === Symbol::CASE) {
            return ReflectionMember::TYPE_ENUM;
        }
        if ($symbolType === Symbol::METHOD) {
            return ReflectionMember::TYPE_METHOD;
        }
        if ($symbolType === Symbol::PROPERTY) {
            return ReflectionMember::TYPE_PROPERTY;
        }
        if ($symbolType === Symbol::VARIABLE) {
            return ReflectionMember::TYPE_PROPERTY;
        }
        if ($symbolType === Symbol::CONSTANT) {
            return ReflectionMember::TYPE_CONSTANT;
        }

        throw new RuntimeException(sprintf(
            'Could not convert symbol type "%s" to member type',
            $symbolType
        ));
    }

    /**
     * @return MemberRecord::TYPE_*
     */
    private function symbolTypeToReferenceType(NodeContext $nodeContext): string
    {
        $symbolType = $nodeContext->symbol()->symbolType();

        if ($symbolType === Symbol::CASE) {
            return MemberRecord::TYPE_CONSTANT;
        }
        if ($symbolType === Symbol::METHOD) {
            return MemberRecord::TYPE_METHOD;
        }
        if ($symbolType === Symbol::PROPERTY) {
            return MemberRecord::TYPE_PROPERTY;
        }
        if ($symbolType === Symbol::VARIABLE) {
            return MemberRecord::TYPE_PROPERTY;
        }
        if ($symbolType === Symbol::CONSTANT) {
            return MemberRecord::TYPE_CONSTANT;
        }

        throw new RuntimeException(sprintf(
            'Could not convert symbol type "%s" to reference type',
            $symbolType
        ));
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
        $class = $this->query->class()->get($containerType);
        if (!$class) {
            return;
        }
        foreach ($class->references() as $reference) {
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
                    Location::fromPathAndOffset(
                        $file->filePath() ?? '',
                        $fileReference->offset()
                    )
                );
            }
        }
    }
}
