<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Application\ClassSearch;
use Phpactor\Rpc\Handler\ClassSearchHandler;
use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\ReturnAction;
use Phpactor\Rpc\Editor\ReturnChoiceAction;

class ClassSearchHandlerTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $classSearch;

    public function setUp()
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
    public function testNoResults()
    {
        $this->classSearch->classSearch('composer', 'AAA')
            ->willReturn([]);

        $action = $this->handle('class_search', [
            'short_name' => 'AAA',
        ]);

        $this->assertInstanceOf(EchoAction::class, $action);
        $this->assertContains('No classes found', $action->message());
    }

    /**
     * If 1 result is found, return the value.
     */
    public function testOneResult()
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

        $this->assertInstanceOf(ReturnAction::class, $action);
        $this->assertEquals([
            'class' => 'Foobar',
        ], $action->value());
    }

    /**
     * Many results, show a choice
     */
    public function testManyResult()
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

        $this->assertInstanceOf(ReturnChoiceAction::class, $action);
        $this->assertCount(2, $action->options());
    }
}

