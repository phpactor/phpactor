<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassReferences;
use Phpactor\Rpc\Handler\ReferencesHandler;
use Phpactor\Container\SourceCodeFilesystemExtension;
use Phpactor\Rpc\Editor\EchoResponse;
use Phpactor\Rpc\Editor\CollectionResponse;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Application\ClassMemberReferences;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Rpc\Editor\InputCallbackResponse;

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

    /**
     * @var FilesystemRegistry
     */
    private $filesystemRegistry;

    public function setUp()
    {
        $this->classReferences = $this->prophesize(ClassReferences::class);
        $this->classMethodReferences = $this->prophesize(ClassMemberReferences::class);
        $this->logger = new ArrayLogger();
        $this->reflector = Reflector::create(new StringSourceLocator(SourceCode::fromPath(__FILE__)), $this->logger);
        $this->filesystemRegistry = $this->prophesize(FilesystemRegistry::class);
    }

    public function createHandler(): Handler
    {
        return new ReferencesHandler(
            $this->reflector,
            $this->classReferences->reveal(),
            $this->classMethodReferences->reveal(),
            $this->filesystemRegistry->reveal()
        );
    }

    public function testFilesystemSelection()
    {
        $this->filesystemRegistry->names()->willReturn(['one', 'two']);

        $action = $this->handle('references', [
            'source' => '<?php',
            'offset' => 1,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $input = reset($inputs);
        $this->assertEquals(ReferencesHandler::PARAMETER_FILESYSTEM, $input->name());
        $this->assertEquals([ 'one' => 'one', 'two' => 'two' ], $input->choices());
        $this->assertEquals('git', $input->default());
    }


    public function testInvalidSymbolType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot find references for symbol');

        $action = $this->handle('references', [
            'source' => '<?php',
            'offset' => 1,
            'filesystem' => 'git',
        ]);
    }

    public function testClassReturnNoneFound()
    {
        $this->classReferences->findReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            'stdClass'
        )->willReturn([
            'references' => [],
            'risky_references' => [],
        ]);

        $action = $this->handle('references', [
            'source' => '<?php new \stdClass();',
            'offset' => 15,
            'filesystem' => 'git',
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
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
                            'col_no' => 12,
                        ],
                    ],
                ]
            ],
        ]);

        $action = $this->handle('references', [
            'source' => '<?php new \stdClass();',
            'offset' => 15,
            'filesystem' => 'git',
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $action);

        $actions = $action->actions();

        $first = array_shift($actions);
        $this->assertInstanceOf(EchoResponse::class, $first);

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
                            'col_no' => 12,
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
            'testMethodReturnNoneFound',
            ClassMemberQuery::TYPE_METHOD
        )->willReturn([
            'references' => [],
        ]);

        $action = $this->handle('references', [
            'source' => $std = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMethodReturnNoneFound();',
            'offset' => 86,
            'filesystem' => 'git',
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
    }

    public function testMethodReferences()
    {
        $this->classMethodReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMethodReferences',
            ClassMemberQuery::TYPE_METHOD
        )->willReturn([
            'references' => [
                [
                    'file' => 'barfoo',
                    'references' => [
                        [
                            'start' => 10,
                            'line_no' => 10,
                            'end' => 20,
                            'col_no' => 12,
                        ],
                    ],
                ]
            ],
        ]);

        $action = $this->handle('references', [
            'source' => $std = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMethodReferences();',
            'offset' => 86,
            'filesystem' => 'git',
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $action);

        $actions = $action->actions();

        $first = array_shift($actions);
        $this->assertInstanceOf(EchoResponse::class, $first);

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
                            'col_no' => 12,
                        ]
                    ],
                ]
            ],
        ], $second->parameters());
    }

    public function testMethodReferencesWithRisky()
    {
        $this->classMethodReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMethodReferences',
            ClassMemberQuery::TYPE_METHOD
        )->willReturn([
            'references' => [
                [
                    'file' => 'barfoo',
                    'references' => [
                        [
                            'start' => 10,
                            'line_no' => 10,
                            'end' => 20,
                            'col_no' => 12,
                        ],
                    ],
                    'risky_references' => [
                        [
                            'start' => 10,
                            'line_no' => 10,
                            'end' => 20,
                            'col_no' => 12,
                        ],
                    ],
                ]
            ],
        ]);

        $action = $this->handle('references', [
            'source' => $std = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMethodReferences();',
            'offset' => 86,
            'filesystem' => 'git',
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $action);

        $actions = $action->actions();

        $first = array_shift($actions);
        $this->assertInstanceOf(EchoResponse::class, $first);
        $this->assertContains('risky', $first->message());
    }
}
