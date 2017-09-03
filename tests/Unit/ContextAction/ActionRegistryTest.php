<?php

namespace Phpactor\Tests\Unit\ContextAction;

use PHPUnit\Framework\TestCase;
use Phpactor\ContextAction\ActionRegistry;
use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;
use Phpactor\ContextAction\Action;

class ActionRegistryTest extends TestCase
{
    /**
     * @var Action
     */
    private $action1;

    public function setUp()
    {
        $this->action1 = $this->prophesize(Action::class);
    }
    /**
     * It returns zero actions when none are available.
     */
    public function testNoActions()
    {
        $registry = $this->createRegistry([]);
        $actions = $registry->actionNames(Symbol::CLASS_);
        $this->assertEmpty($actions);
    }

    /**
     * It lists available actions for a given type.
     */

    public function testActionNames()
    {
        $registry = $this->createRegistry([
            Symbol::CLASS_ => [
                'aaa' => $this->action1->reveal()
            ]
        ]);

        $actions = $registry->actionNames(Symbol::CLASS_);
        $this->assertEquals([ 'aaa' ], $actions);
    }

    /**
     * It throws an exception if the symbol type does not exist.
     */
    public function testThrowExceptionNoActionsForSymbol()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('No actions for symbol type "class"');
        $registry = $this->createRegistry([]);
        $registry->action(Symbol::CLASS_, 'foobar');
    }

    /**
     * It throws an exception if action for symbol does not exist
     */
    public function testThrowExceptionUnknownActionForSymbol()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('No action "foobar" for symbol type "class", available actions: "aaa"');

        $registry = $this->createRegistry([
            Symbol::CLASS_ => [
                'aaa' => $this->action1->reveal()
            ]
        ]);
        $registry->action(Symbol::CLASS_, 'foobar');
    }

    /**
     * It throws an exception when requested action is not available.
     */
    private function createRegistry(array $actionsByType)
    {
        return new ActionRegistry($actionsByType);
    }
}

