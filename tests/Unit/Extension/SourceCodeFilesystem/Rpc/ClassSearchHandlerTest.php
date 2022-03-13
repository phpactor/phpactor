<?php

namespace Phpactor\Tests\Unit\Extension\SourceCodeFilesystem\Rpc;

use Phpactor\Extension\SourceCodeFilesystemExtra\SourceCodeFilestem\Application\ClassSearch;
use Phpactor\Extension\SourceCodeFilesystemExtra\Rpc\ClassSearchHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\ReturnResponse;
use Phpactor\Extension\Rpc\Response\ReturnChoiceResponse;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ClassSearchHandlerTest extends HandlerTestCase
{
    private ObjectProphecy $classSearch;

    public function setUp(): void
    {
        $this->classSearch = $this->prophesize(ClassSearch::class);
    }

    public function createHandler(): Handler
    {
        return new ClassSearchHandler(
            $this->classSearch->reveal()
        );
    }

    /**
     * If not results are found, echo a message
     */
    public function testNoResults(): void
    {
        $this->classSearch->classSearch('composer', 'AAA')
            ->willReturn([]);

        $action = $this->handle('class_search', [
            'short_name' => 'AAA',
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
        $this->assertStringContainsString('No classes found', $action->message());
    }

    /**
     * If 1 result is found, return the value.
     */
    public function testOneResult(): void
    {
        $this->classSearch->classSearch('composer', 'AAA')
            ->willReturn([
                [
                    'class' => 'Foobar',
                ]
            ]);

        $action = $this->handle('class_search', [
            'short_name' => 'AAA',
        ]);

        $this->assertInstanceOf(ReturnResponse::class, $action);
        $this->assertEquals([
            'class' => 'Foobar',
        ], $action->value());
    }

    /**
     * Many results, show a choice
     */
    public function testManyResult(): void
    {
        $this->classSearch->classSearch('composer', 'AAA')
            ->willReturn([
                [
                    'class' => 'AAA',
                ],
                [
                    'class' => 'BBB',
                ],
            ]);

        $action = $this->handle('class_search', [
            'short_name' => 'AAA',
        ]);

        $this->assertInstanceOf(ReturnChoiceResponse::class, $action);
        $this->assertCount(2, $action->options());
    }
}
