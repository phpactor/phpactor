<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Util;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;

final class OffsetExtractor
{
    private array $points = [];

    private array $rangeOpenMarkers = [];

    private array $rangeCloseMarkers = [];

    public static function create(): OffsetExtractor
    {
        return new OffsetExtractor();
    }

    public function registerOffset(string $name, string $marker): OffsetExtractor
    {
        $this->points[$marker] = $name;
        return $this;
    }

    public function registerRange(string $name, string $openMarker, string $closeMarker): OffsetExtractor
    {
        $this->rangeOpenMarkers[$openMarker] = $name;
        $this->rangeCloseMarkers[$closeMarker] = $name;
        return $this;
    }

    public function parse(string $source): OffsetExtractorResult
    {
        $markers = array_merge(
            array_keys($this->points),
            array_keys($this->rangeOpenMarkers),
            array_keys($this->rangeCloseMarkers),
        );
        $results = preg_split('/('. implode('|', $markers) .')/u', $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        if (!is_array($results)) {
            return $this;
        }

        $newSource = '';
        $pointResults = [];
        $rangeResults = [];
        $offset = 0;
        $currentRangeStartOffset = 0;

        foreach ($this->points as $marker=>$name) {
            $pointResults[$name] = [];
        }
        foreach ($this->rangeCloseMarkers as $marker=>$name) {
            $rangeResults[$name] = [];
        }

        foreach ($results as $result) {
            if (isset($this->points[$result])) {
                $pointResults[$this->points[$result]][] = ByteOffset::fromInt($offset);
                continue;
            }
            
            if (isset($this->rangeOpenMarkers[$result])) {
                $currentRangeStartOffset = $offset;
                continue;
            }

            if (isset($this->rangeCloseMarkers[$result])) {
                $rangeResults[$this->rangeCloseMarkers[$result]][] = ByteOffsetRange::fromInts($currentRangeStartOffset, $offset);
                continue;
            }
            
            $offset += strlen($result);
            $newSource .= $result;
        }
        
        return new OffsetExtractorResult($newSource, $pointResults, $rangeResults);
        ;
    }
}
