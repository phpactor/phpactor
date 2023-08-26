<?php

namespace Phpactor\Extension\LanguageServerBridge\Converter;

use Phpactor\Extension\LanguageServerBridge\Converter\Exception\CouldNotLoadFileContents;
use Phpactor\LanguageServerProtocol\Location as LspLocation;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\LocationRanges;
use Phpactor\TextDocument\TextDocumentLocator;

class LocationConverter
{
    public function __construct(private TextDocumentLocator $locator)
    {
    }

    /**
     * @return array<LspLocation>
     */
    public function toLspLocationsWithRange(LocationRanges $ranges): array
    {
        $lspRanges = [];
        foreach ($ranges as $location) {
            try {
                $lspRanges[] = $this->toLspLocationWithRange($location);
            } catch (TextDocumentNotFound) {
                continue;
            } catch (CouldNotLoadFileContents) {
                continue;
            }
        }

        return $lspRanges;
    }

    public function toLspLocationWithRange(LocationRange $range): LspLocation
    {
        $textDocument = $this->locator->get($range->uri());
        $lspRange = RangeConverter::toLspRange($range->range(), $textDocument->__toString());

        return new LspLocation($range->uri()->__toString(), $lspRange);
    }
}
