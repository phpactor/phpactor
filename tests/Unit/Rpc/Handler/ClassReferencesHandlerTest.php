<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassReferences;
use Phpactor\Rpc\Handler\ClassReferencesHandler;
use Phpactor\Container\SourceCodeFilesystemExtension;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\FileReferencesAction;
use Phpactor\Rpc\Editor\StackAction;

class ClassReferencesHandlerTest extends HandlerTestCase
{
    /**
     * @var ClassReferences
     */
    private $classReferences;

    public function setUp()
    {
        $this->classReferences = $this->prophesize(ClassReferences::class);
    }

    public function createHandler(): Handler
    {
        return new ClassReferencesHandler(
            $this->classReferences->reveal()
        );
    }

    public function testReturnNoneFound()
    {
        $this->classReferences->findReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            'AAA'
        )->willReturn([
            'references' => [],
        ]);

        $action = $this->handle('class_references', [
            'class' => 'AAA',
        ]);

        $this->assertInstanceOf(EchoAction::class, $action);
    }

    public function testReferences()
    {
        $this->classReferences->findReferences(
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            'AAA'
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

        $action = $this->handle('class_references', [
            'class' => 'AAA',
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
