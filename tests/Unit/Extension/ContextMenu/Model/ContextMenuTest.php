<?php

namespace Phpactor\Tests\Unit\Extension\ContextMenu\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ContextMenu\Model\ContextMenu;
use RuntimeException;

class ContextMenuTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $menu = ContextMenu::fromArray([
            'actions' => [
                'do_something' => [
                    'action' => 'blah',
                    'key' => 'a',
                    'parameters' => [
                        'path' => '%path%',
                    ],
                ],
            ],
            'contexts' => [
                'class' => [
                    'do_something',
                ],
            ],
        ]);
        self::assertNotNull($menu);
    }

    public function testExceptionIfKeyIsRepeatedInContext(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Key "b" in context "foo" mapped by action "action2" is already used by action "action1"');
        ContextMenu::fromArray([
            'actions' => [
                'action1' => [
                    'action' => 'blah',
                    'key' => 'b',
                ],
                'action2' => [
                    'action' => 'blah',
                    'key' => 'b',
                ],
            ],
            'contexts' => [
                'foo' => [
                    'action1',
                    'action2',
                ],
            ],
        ]);
    }

    public function testActionDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Action "a" used in context "foo" does not exist');
        ContextMenu::fromArray([
            'actions' => [
                'b' => [
                    'action' => 'blah',
                    'key' => 'b',
                ],
            ],
            'contexts' => [
                'foo' => [
                    'a',
                    'b',
                ],
            ],
        ]);
    }
}
