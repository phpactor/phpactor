<?php
namespace Phpactor\Extension\ReferenceFinderRpc\Tests\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ReferenceFinderRpc\Handler\GotoDefinitionHandler;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Phpactor\ReferenceFinder\DefinitionLocation;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class GotoDefinitionHandlerTest extends TestCase
{
    const EXAMPLE_SOURCE = 'some source file';
    const EXAMPLE_OFFSET = 1234;
    const EXAMPLE_PATH = '/some/path.php';


    public function testGotoDefinition(): void
    {
        $location = $this->create()->handle('goto_definition', [
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
        $locator = new class implements DefinitionLocator {
            public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): DefinitionLocation
            {
                return new DefinitionLocation($document->uri(), $byteOffset);
            }
        };
        return new HandlerTester(new GotoDefinitionHandler($locator));
    }
}
