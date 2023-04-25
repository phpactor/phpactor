<?php

declare(strict_types=1);

namespace Phpactor\Extension\ReferenceFinderRpc;

use Phpactor\Extension\Rpc\Response;
use Phpactor\Extension\Rpc\Response\FileReferencesResponse;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Response\Reference\FileReferences;
use Phpactor\Extension\Rpc\Response\Reference\Reference;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\Util\LineAtOffset;
use Phpactor\TextDocument\Location;
use RuntimeException;

final class LocationSelector
{
    public function selectFileOpenResponse(Locations $locations, string $targetToOpen): Response
    {
        if (count($locations) !== 1) {
            $references = $this->locationsToReferences($locations);

            return new FileReferencesResponse($references);
        }

        $location = $locations->first();
        return OpenFileResponse::fromPathAndOffset(
            $location->uri()->path(),
            $location->offset()->toInt()
        )->withTarget($targetToOpen);
    }

    /**
     * @return array<FileReferences>
     */
    private function locationsToReferences(Locations $locations): array
    {
        $references = [];
        foreach ($locations as $location) {
            $contents = $this->fileContents($location);
            $lineCol = LineCol::fromByteOffset($contents, ByteOffset::fromInt($location->offset()->toInt()));
            $line = (new LineAtOffset())->__invoke($contents, $location->offset()->toInt());

            $fileReferences = FileReferences::fromPathAndReferences(
                $location->uri()->path(),
                [
                    Reference::fromStartEndLineNumberLineAndCol(
                        $location->offset()->toInt(),
                        $location->offset()->toInt(),
                        $lineCol->line(),
                        $line,
                        $lineCol->col()
                    ),
                ]
            );
            $references[] = $fileReferences;
        }

        return $references;
    }

    private function fileContents(Location $location): string
    {
        $content = @file_get_contents($location->uri()->path());
        if ($content === false) {
            throw new RuntimeException(sprintf(
                'Could not open file "%s"',
                $location->uri()->path()
            ));
        }

        return $content;
    }
}
