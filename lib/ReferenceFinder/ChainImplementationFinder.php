<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\Locations;

final class ChainImplementationFinder implements ClassImplementationFinder
{
    /**
     * @var ClassImplementationFinder[]
     */
    private array $finders = [];

    /**
     * @param ClassImplementationFinder[] $finders
     */
    public function __construct(array $finders)
    {
        foreach ($finders as $finder) {
            $this->add($finder);
        }
    }

    public function findImplementations(TextDocument $document, ByteOffset $byteOffset, bool $includeDefinition = false): Locations
    {
        $messages = [];
        $locations = [];
        foreach ($this->finders as $finder) {
            $locations = array_merge(
                $locations,
                iterator_to_array(
                    $finder->findImplementations(
                        $document,
                        $byteOffset,
                        $includeDefinition
                    )
                )
            );
        }

        return new Locations($locations);
    }

    private function add(ClassImplementationFinder $finder): void
    {
        $this->finders[] = $finder;
    }
}
