<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassReferences;
use Phpactor\Rpc\Handler\ReferencesHandler;
use Phpactor\Container\SourceCodeFilesystemExtension;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\FileReferencesAction;
use Phpactor\Rpc\Editor\StackAction;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Application\ClassMethodReferences;

class ClassReferencesHandlerTest extends HandlerTestCase
{
    /**
     * @var ClassReferences
     */
    private $classReferences;

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var ObjectProphecy
     */
    private $classMethodReferences;

    public function setUp()
    {
        $this->classReferences = $this->prophesize(ClassReferences::class);
        $this->classMethodReferences = $this->prophesize(ClassMethodReferences::class);
        $this->reflector = Reflector::create(new StringSourceLocator(SourceCode::fromPath(__FILE__)));
    }

    public function createHandler(): Handler
    {
        return new ReferencesHandler(
            $this->reflector,
            $this->classReferences->reveal(),
            $this->classMethodReferences->reveal()
        );
    }

    public function testReturnNoneFound()
    {
        $this->classReferences->findReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            'stdClass'
        )->willReturn([
            'references' => [],
        ]);

        $action = $this->handle('references', [
            'source' => '<?php new \stdClass();',
            'offset' => 15,
        ]);

        $this->assertInstanceOf(EchoAction::class, $action);
    }

    public function testReferences()
    {
        $this->classReferences->findReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            'stdClass'
        )->willReturn([
            'references' => [
                [
                    'file' => 'barfoo',
                    'references' => [
                        [
                            'start' => 10,
                            'line_no' => 10,
                            'end' => 20,
                        ],
                    ],
                ]
            ],
        ]);

        $action = $this->handle('references', [
            'source' => '<?php new \stdClass();',
            'offset' => 15,
        ]);

        $this->assertInstanceOf(StackAction::class, $action);

        $actions = $action->actions();

        $first = array_shift($actions);
        $this->assertInstanceOf(EchoAction::class, $first);

        $second = array_shift($actions);
        $this->assertEquals([
            'file_references' => [
                [
                    'file' => 'barfoo',
                    'references' => [
                        [
                            'start' => 10,
                            'end' => 20,
                            'line_no' => 10,
                        ]
                    ],
                ]
            ],
        ], $second->parameters());
    }
}

