<?php
namespace Phpactor\Extension\ReferenceFinderRpc\Tests\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ReferenceFinderRpc\Handler\GotoImplementationHandler;
use Phpactor\Extension\Rpc\Response\FileReferencesResponse;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\LocationRanges;
use Phpactor\TextDocument\TextDocument;

class GotoImplementationHandlerTest extends TestCase
{
    const EXAMPLE_SOURCE = 'some source file';
    const EXAMPLE_OFFSET = 1234;
    const EXAMPLE_PATH = '/some/path.php';

    public function testGotoSingleImplementation(): void
    {
        $location = $this->create([
            LocationRange::fromPathAndOffsets(self::EXAMPLE_PATH, 10, 10)
        ])->handle('goto_implementation', [
            'source' => self::EXAMPLE_SOURCE,
            'offset' => self::EXAMPLE_OFFSET,
            'path' => self::EXAMPLE_PATH,
            'target' => OpenFileResponse::TARGET_HORIZONTAL_SPLIT,
        ]);

        $this->assertInstanceOf(OpenFileResponse::class, $location);
        $this->assertEquals(self::EXAMPLE_PATH, $location->path());
        $this->assertEquals(OpenFileResponse::TARGET_HORIZONTAL_SPLIT, $location->target());
    }

    public function testSelectFromMultiple(): void
    {
        $response = $this->create([
            LocationRange::fromPathAndOffsets(__FILE__, 20, 20),
            LocationRange::fromPathAndOffsets(__FILE__, 40, 40)
        ])->handle('goto_implementation', [
            'source' => self::EXAMPLE_SOURCE,
            'offset' => self::EXAMPLE_OFFSET,
            'path' => self::EXAMPLE_PATH,
            'target' => OpenFileResponse::TARGET_HORIZONTAL_SPLIT,
        ]);

        $this->assertInstanceOf(FileReferencesResponse::class, $response);
    }

    /**
     * @param LocationRange[] $locations
     */
    public function create(array $locations): HandlerTester
    {
        $locator = new class($locations) implements ClassImplementationFinder {
            /**
             * @param LocationRange[] $locations
             */
            public function __construct(private array $locations)
            {
            }

            public function findImplementations(TextDocument $document, ByteOffset $byteOffset, bool $includeDefinition = false): LocationRanges
            {
                return new LocationRanges($this->locations);
            }
        };

        return new HandlerTester(new GotoImplementationHandler($locator));
    }
}
