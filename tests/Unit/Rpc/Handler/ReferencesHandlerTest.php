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
use Symfony\Component\Yaml\Exception\RuntimeException;
use Phpactor\Application\ClassMemberReferences;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;

class ReferencesHandlerTest extends HandlerTestCase
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
     * @var ClassMethodReferences
     */
    private $classMethodReferences;

    private $logger;

    public function setUp()
    {
        $this->classReferences = $this->prophesize(ClassReferences::class);
        $this->classMethodReferences = $this->prophesize(ClassMemberReferences::class);
        $this->logger = new ArrayLogger();
        $this->reflector = Reflector::create(new StringSourceLocator(SourceCode::fromPath(__FILE__)), $this->logger);
    }

    public function tearDown()
    {
    }

    public function createHandler(): Handler
    {
        return new ReferencesHandler(
            $this->reflector,
            $this->classReferences->reveal(),
            $this->classMethodReferences->reveal()
        );
    }

    public function testInvalidSymbolType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot find references for symbol');

        $action = $this->handle('references', [
            'source' => '<?php',
            'offset' => 1,
        ]);
    }

    public function testClassReturnNoneFound()
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

    public function testClassReferences()
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

    public function testMethodReturnNoneFound()
    {
        $this->classMethodReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMethodReturnNoneFound'
        )->willReturn([
            'references' => [],
        ]);

        $action = $this->handle('references', [
            'source' => $std = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMethodReturnNoneFound();',
            'offset' => 86,
        ]);

        $this->assertInstanceOf(EchoAction::class, $action);
    }

    public function testMethodReferences()
    {
        $this->classMethodReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMethodReferences'
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
            'source' => $std = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMethodReferences();',
            'offset' => 86,
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

