<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassReferences;
use Phpactor\Rpc\Handler\ReferencesHandler;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Rpc\Response\EchoResponse;
use Phpactor\Rpc\Response\CollectionResponse;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Application\ClassMemberReferences;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Rpc\Response\InputCallbackResponse;
use Phpactor\Rpc\Response\Input\TextInput;
use Phpactor\WorseReflection\ReflectorBuilder;

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
     * @var ClassMemberReferences
     */
    private $classMemberReferences;

    /**
     * @var ArrayLogger
     */
    private $logger;

    /**
     * @var FilesystemRegistry
     */
    private $filesystemRegistry;

    public function setUp()
    {
        $this->classReferences = $this->prophesize(ClassReferences::class);
        $this->classMemberReferences = $this->prophesize(ClassMemberReferences::class);
        $this->logger = new ArrayLogger();
        $this->reflector = ReflectorBuilder::create()->addSource(SourceCode::fromPath(__FILE__))->withLogger($this->logger)->build();
        $this->filesystemRegistry = $this->prophesize(FilesystemRegistry::class);
    }

    public function createHandler(): Handler
    {
        return new ReferencesHandler(
            $this->reflector,
            $this->classReferences->reveal(),
            $this->classMemberReferences->reveal(),
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
        $this->classReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            'stdClass',
            null
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
        $this->classReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            'stdClass',
            null
        )->willReturn($this->exampleClassResponse());

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

    public function testReplaceClassReferences()
    {
        $this->classReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            'stdClass',
            'newClass',
            null
        )->willReturn($this->exampleClassResponse());

        $action = $this->handle('references', [
            'source' => '<?php new \stdClass();',
            'offset' => 15,
            'filesystem' => 'git',
            'mode' => ReferencesHandler::MODE_REPLACE,
            'replacement' => 'newClass',
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $action);
    }

    public function testMemberReturnNoneFound()
    {
        $this->classMemberReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMemberReturnNoneFound',
            ClassMemberQuery::TYPE_METHOD,
            null
        )->willReturn([
            'references' => [],
        ]);

        $action = $this->handle('references', [
            'source' => $std = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMemberReturnNoneFound();',
            'offset' => 86,
            'filesystem' => 'git',
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
    }

    public function testMemberReferences()
    {
        $this->classMemberReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMemberReferences',
            ClassMemberQuery::TYPE_METHOD,
            null
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
            'source' => $std = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMemberReferences();',
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

    public function testReplaceMemberDemandReplacement()
    {
        $replacement = 'foobar';

        $this->classMemberReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMemberReferences',
            ClassMemberQuery::TYPE_METHOD,
            $replacement
        )->willReturn($this->exampleMemberRiskyResponse());

        $action = $this->handle('references', [
            'source' => '<?php $foo = new ' . __CLASS__ . '(); $foo->testMemberReferences();',
            'offset' => 86,
            'filesystem' => 'git',
            'mode' => ReferencesHandler::MODE_REPLACE,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $textInput = $action->inputs()[0];
        $this->assertInstanceOf(TextInput::class, $textInput);
        $this->assertEquals('testMemberReferences', $textInput->default());
    }

    public function testReplaceMember()
    {
        $replacement = 'foobar';

        $this->classMemberReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMemberReferences',
            ClassMemberQuery::TYPE_METHOD,
            $replacement
        )->willReturn($this->exampleMemberRiskyResponse());

        $action = $this->handle('references', [
            'source' => '<?php $foo = new ' . __CLASS__ . '(); $foo->testMemberReferences();',
            'offset' => 86,
            'filesystem' => 'git',
            'mode' => ReferencesHandler::MODE_REPLACE,
            'replacement' => $replacement,
        ]);
    }

    public function testMemberReferencesWithRisky()
    {
        $this->classMemberReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMemberReferences',
            ClassMemberQuery::TYPE_METHOD,
            null
        )->willReturn($this->exampleMemberRiskyResponse());

        $action = $this->handle('references', [
            'source' => $std = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMemberReferences();',
            'offset' => 86,
            'filesystem' => 'git',
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $action);

        $actions = $action->actions();

        $first = array_shift($actions);
        $this->assertInstanceOf(EchoResponse::class, $first);
        $this->assertContains('risky', $first->message());
    }

    private function exampleMemberRiskyResponse()
    {
        return [
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
        ];
    }

    private function exampleClassResponse()
    {
        return [
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
        ];
    }
}
