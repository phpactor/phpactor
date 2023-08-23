<?php
namespace Phpactor\Extension\ReferenceFinderRpc\Tests\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ReferenceFinderRpc\Handler\GotoTypeHandler;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Type\MixedType;

class GotoTypeHandlerTest extends TestCase
{
    const EXAMPLE_SOURCE = 'some source file';
    const EXAMPLE_OFFSET = 1234;
    const EXAMPLE_PATH = '/some/path.php';

    public function testGotoType(): void
    {
        $location = $this->create()->handle('goto_type', [
            'source' => self::EXAMPLE_SOURCE,
            'offset' => self::EXAMPLE_OFFSET,
            'path' => self::EXAMPLE_PATH,
            'target' => OpenFileResponse::TARGET_HORIZONTAL_SPLIT,
        ]);

        $this->assertInstanceOf(OpenFileResponse::class, $location);
        $this->assertEquals(self::EXAMPLE_PATH, $location->path());
        $this->assertEquals(OpenFileResponse::TARGET_HORIZONTAL_SPLIT, $location->target());
    }

    public function create(): HandlerTester
    {
        $locator = new class implements TypeLocator {
            public function locateTypes(TextDocument $document, ByteOffset $byteOffset): TypeLocations
            {
                return
                    new TypeLocations([
                        new TypeLocation(
                            new MixedType(),
                            new LocationRange(
                                $document->uriOrThrow(),
                                ByteOffsetRange::fromByteOffsets($byteOffset, $byteOffset)
                            ),
                        )
                    ]);
            }
        };
        return new HandlerTester(new GotoTypeHandler($locator));
    }
}
