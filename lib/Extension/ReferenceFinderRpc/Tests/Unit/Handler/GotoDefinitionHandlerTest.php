<?php
namespace Phpactor\Extension\ReferenceFinderRpc\Tests\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ReferenceFinderRpc\Handler\GotoDefinitionHandler;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Phpactor\ReferenceFinder\TestDefinitionLocator;
use Phpactor\TextDocument\Location;
use Phpactor\WorseReflection\Core\TypeFactory;

class GotoDefinitionHandlerTest extends TestCase
{
    private const EXAMPLE_SOURCE = 'some source file';
    private const EXAMPLE_OFFSET = 1234;
    private const EXAMPLE_PATH = '/some/path.php';

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
        $location = Location::fromPathAndOffsets(self::EXAMPLE_PATH, self::EXAMPLE_OFFSET, self::EXAMPLE_OFFSET);

        return new HandlerTester(
            new GotoDefinitionHandler(
                TestDefinitionLocator::fromSingleLocation(TypeFactory::unknown(), $location)
            )
        );
    }
}
