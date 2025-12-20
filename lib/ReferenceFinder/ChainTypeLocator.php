<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\ReferenceFinder\Exception\CouldNotLocateType;
use Phpactor\ReferenceFinder\Exception\UnsupportedDocument;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ChainTypeLocator implements TypeLocator
{
    /**
     * @var TypeLocator[]
     */
    private array $locators = [];

    /**
     * @param TypeLocator[] $locators
     */
    public function __construct(
        array $locators,
        private LoggerInterface $logger = new NullLogger(),
    ) {
        foreach ($locators as $locator) {
            $this->add($locator);
        }
    }

    public function locateTypes(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        $messages = [];
        foreach ($this->locators as $locator) {
            try {
                $typeLocations = $locator->locateTypes($document, $byteOffset);
            } catch (UnsupportedDocument $unsupported) {
                $this->logger->debug(sprintf(
                    'Document is unsupported by "%s": %s',
                    get_class($locator),
                    $unsupported->getMessage()
                ));
                $messages[] = $unsupported->getMessage();
                continue;
            }

            if (!$typeLocations->count()) {
                continue;
            }

            return $typeLocations;
        }

        if ($messages) {
            throw new CouldNotLocateType(implode(', ', $messages));
        }

        throw new CouldNotLocateType('No type locators are registered');
    }

    private function add(TypeLocator $locator): void
    {
        $this->locators[] = $locator;
    }
}
