<?php

namespace Phpactor\Extension\ReferenceFinderRpc\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ReferenceFinderRpc\LocationSelector;
use Phpactor\Extension\Rpc\Response\FileReferencesResponse;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use RuntimeException;

class LocationSelectorTest extends TestCase
{
    private LocationSelector $locationSelector;

    public function setUp(): void
    {
        $this->locationSelector = new LocationSelector();
    }

    public function testGeneratingResponseForNoEntries(): void
    {
        $target = OpenFileResponse::TARGET_FOCUSED_WINDOW;
        $locations = new Locations([]);

        $response = $this->locationSelector->selectFileOpenResponse($locations, $target);

        self::assertInstanceOf(FileReferencesResponse::class, $response);

        /** @var FileReferencesResponse $response */
        self::assertCount(0, $response->references());
        self::assertSame('file_references', $response->name());
    }

    public function testGeneratingResponseWithNonExistingPath(): void
    {
        $target = OpenFileResponse::TARGET_FOCUSED_WINDOW;
        $locations = new Locations([
            Location::fromPathAndOffset('/non_existing_path', 13),
            Location::fromPathAndOffset('/non_existing_path', 1),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not open file "/non_existing_path"');

        $this->locationSelector->selectFileOpenResponse($locations, $target);
    }

    public function testGeneratingResponseForOneEntry(): void
    {
        $target = OpenFileResponse::TARGET_FOCUSED_WINDOW;
        $locations = new Locations([
            Location::fromPathAndOffset('/somePath', 13),
        ]);
        $response = $this->locationSelector->selectFileOpenResponse($locations, $target);

        self::assertInstanceOf(OpenFileResponse::class, $response);

        /** @var OpenFileResponse $response */
        self::assertEquals('/somePath', $response->path());
        self::assertEquals($target, $response->target());
    }

    public function testGeneratingResponseForMultipleEntries(): void
    {
        $target = OpenFileResponse::TARGET_FOCUSED_WINDOW;
        $locations = new Locations([
            Location::fromPathAndOffset(__FILE__, 13),
            Location::fromPathAndOffset(__FILE__, 1),
        ]);

        $response = $this->locationSelector->selectFileOpenResponse($locations, $target);

        self::assertInstanceOf(FileReferencesResponse::class, $response);

        /** @var FileReferencesResponse $response */
        self::assertCount(2, $response->references());
        foreach($response->references() as $reference) {
            $config = $reference->toArray();
            self::assertSame(__FILE__, $config['file']);
            self::assertCount(1, $config['references']);
        }

        self::assertSame('file_references', $response->name());
    }
}
