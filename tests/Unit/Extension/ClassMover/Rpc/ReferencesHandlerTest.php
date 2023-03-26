<?php

namespace Phpactor\Tests\Unit\Extension\ClassMover\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\ClassMover\Application\ClassReferences;
use Phpactor\Extension\ClassMover\Rpc\ReferencesHandler;
use Phpactor\Extension\Rpc\Response\FileReferencesResponse;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Extension\ClassMover\Application\ClassMemberReferences;
use Phpactor\WorseReflection\Bridge\PsrLog\ArrayLogger;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;

class ReferencesHandlerTest extends HandlerTestCase
{
    const TEST_PATH = 'file:///test_file.php';

    private ObjectProphecy $classReferences;

    private Reflector $reflector;

    private ObjectProphecy $classMemberReferences;

    private ArrayLogger $logger;

    private ObjectProphecy $filesystemRegistry;

    public function setUp(): void
    {
        $this->classReferences = $this->prophesize(ClassReferences::class);
        $this->classMemberReferences = $this->prophesize(ClassMemberReferences::class);
        $this->logger = new ArrayLogger();
        $this->reflector = ReflectorBuilder::create()->addSource(TextDocumentBuilder::fromUri(__FILE__)->build())->withLogger($this->logger)->build();
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

    public function testFilesystemSelection(): void
    {
        $this->filesystemRegistry->names()->willReturn(['one', 'two']);

        $action = $this->handle('references', [
            'source' => '<?php',
            'offset' => 2173,
            'path' => self::TEST_PATH,
            'filesystem' => null,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $input = reset($inputs);
        $this->assertEquals(ReferencesHandler::PARAMETER_FILESYSTEM, $input->name());
        $this->assertEquals([ 'one' => 'one', 'two' => 'two' ], $input->choices());
        $this->assertEquals('git', $input->default());
    }

    public function testInvalidSymbolType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot find references for symbol');

        $action = $this->handle('references', [
            'source' => '<?php',
            'offset' => 1,
            'filesystem' => 'git',
            'path' => self::TEST_PATH,
        ]);
    }

    public function testClassReturnNoneFound(): void
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
            'path' => self::TEST_PATH,
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
    }

    public function testClassReferences(): void
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
            'path' => self::TEST_PATH,
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
                            'line' => '',
                            'line_no' => 10,
                            'col_no' => 12,
                        ]
                    ],
                ]
            ],
        ], $second->parameters());
    }

    public function testReplaceClassReferences(): void
    {
        $source = '<?php new \stdClass();';
        $this->classReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            'stdClass',
            'newClass',
            null
        )->willReturn($this->exampleClassResponse());

        $this->classReferences->replaceInSource(
            $source,
            'stdClass',
            'newClass'
        )->willReturn($source);

        $action = $this->handle('references', [
            'source' => $source,
            'offset' => 15,
            'filesystem' => 'git',
            'path' => self::TEST_PATH,
            'mode' => ReferencesHandler::MODE_REPLACE,
            'replacement' => 'newClass',
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $action);
    }

    public function testMemberReturnNoneFound(): void
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
            'offset' => 104,
            'path' => self::TEST_PATH,
            'filesystem' => 'git',
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
    }

    public function testMemberReferences(): void
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
                            'line' => '',
                            'col_no' => 12,
                        ],
                    ],
                ]
            ],
        ]);

        $action = $this->handle('references', [
            'source' => $std = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMemberReferences();',
            'offset' => 104,
            'path' => self::TEST_PATH,
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
                            'line' => '',
                            'line_no' => 10,
                            'col_no' => 12,
                        ]
                    ],
                ]
            ],
        ], $second->parameters());
    }

    public function testReplaceMemberDemandReplacement(): void
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
            'offset' => 104,
            'path' => self::TEST_PATH,
            'filesystem' => 'git',
            'mode' => ReferencesHandler::MODE_REPLACE,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $textInput = $action->inputs()[0];
        $this->assertInstanceOf(TextInput::class, $textInput);
        $this->assertEquals('testMemberReferences', $textInput->default());
    }

    public function testReplaceMember(): void
    {
        $replacement = 'foobar';
        $source = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMemberReferences();';

        $this->classMemberReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMemberReferences',
            ClassMemberQuery::TYPE_METHOD,
            $replacement
        )->willReturn($this->exampleMemberRiskyResponse());

        $this->classMemberReferences->replaceInSource(
            $source,
            __CLASS__,
            'testMemberReferences',
            ClassMemberQuery::TYPE_METHOD,
            $replacement
        )->willReturn('<?php hallo');

        $action = $this->handle('references', [
            'source' => $source,
            'path' => self::TEST_PATH,
            'offset' => 104,
            'filesystem' => 'git',
            'mode' => ReferencesHandler::MODE_REPLACE,
            'replacement' => $replacement,
        ]);

        assert($action instanceof CollectionResponse);
        $first = $action->actions()[0];
        $this->assertInstanceOf(EchoResponse::class, $first);
        $second = $action->actions()[1];
        $this->assertInstanceOf(UpdateFileSourceResponse::class, $second);
        assert($second instanceof UpdateFileSourceResponse);
        $third = $action->actions()[2];
        $this->assertEquals('<?php hallo', $second->newSource());
        $this->assertInstanceOf(FileReferencesResponse::class, $third);
    }

    public function testMemberReferencesWithRisky(): void
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
            'path' => self::TEST_PATH,
            'offset' => 104,
            'filesystem' => 'git',
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $action);

        $actions = $action->actions();

        $first = array_shift($actions);
        $this->assertInstanceOf(EchoResponse::class, $first);
        $this->assertStringContainsString('risky', $first->message());
    }

    public function testReferencesAreSorted(): void
    {
        $this->classMemberReferences->findOrReplaceReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            __CLASS__,
            'testMemberReferences',
            ClassMemberQuery::TYPE_METHOD,
            null
        )->willReturn([
            'references' => [[
                'file' => 'foobar',
                'references' => [
                    [
                        'start' => 10,
                        'line_no' => 8,
                        'end' => 20,
                        'line' => '',
                        'col_no' => 12,
                    ],
                ],
            ], [
                'file' => 'barfoo',
                'references' => [
                    [
                        'start' => 13,
                        'line_no' => 10,
                        'end' => 20,
                        'line' => '',
                        'col_no' => 15,
                    ], [
                        'start' => 10,
                        'line_no' => 10,
                        'end' => 20,
                        'line' => '',
                        'col_no' => 12,
                    ],
                ],
            ]],
        ]);

        $action = $this->handle('references', [
            'source' => $std = '<?php $foo = new ' . __CLASS__ . '(); $foo->testMemberReferences();',
            'offset' => 104,
            'path' => self::TEST_PATH,
            'filesystem' => 'git',
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $action);

        $actions = $action->actions();

        $first = array_shift($actions);
        $this->assertInstanceOf(EchoResponse::class, $first);

        $second = array_shift($actions);
        $this->assertEquals([
            'file_references' => [[
                'file' => 'barfoo',
                'references' => [
                    [
                        'start' => 10,
                        'line_no' => 10,
                        'end' => 20,
                        'line' => '',
                        'col_no' => 12,
                    ], [
                        'start' => 13,
                        'line_no' => 10,
                        'end' => 20,
                        'line' => '',
                        'col_no' => 15,
                    ],
                ],
            ], [
                'file' => 'foobar',
                'references' => [
                    [
                        'start' => 10,
                        'line_no' => 8,
                        'end' => 20,
                        'line' => '',
                        'col_no' => 12,
                    ],
                ],
            ]],
        ], $second->parameters());
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
