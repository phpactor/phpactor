<?php

namespace Phpactor\Extension\ReferenceFinderRpc\Tests\Unit\Handler;

use Phpactor\Extension\ReferenceFinderRpc\Handler\GotoDefinitionHandler;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Phpactor\ReferenceFinder\DefinitionLocation;
use Phpactor\ReferenceFinder\TestDefinitionLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\TypeFactory;
use PHPUnit\Framework\TestCase;

class GotoDefinitionHandlerTest extends TestCase
{
    public const EXAMPLE_SOURCE = 'some source file';
    public const EXAMPLE_OFFSET = 1234;
    public const EXAMPLE_PATH = '/some/path.php';

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
        $location = new DefinitionLocation(TextDocumentUri::fromString(self::EXAMPLE_PATH), ByteOffset::fromInt(1));
        return new HandlerTester(
            new GotoDefinitionHandler(
                TestDefinitionLocator::fromSingleLocation(
                    TypeFactory::unknown(),
                    $location
                )
            )
        );
    }
}
