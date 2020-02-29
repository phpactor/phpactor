<?php

namespace Phpactor\Tests\Unit\Extension\ContextMenu\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ContextMenu\Model\ContextMenu;

class ContextMenuTest extends TestCase
{
    public function testCreateFromArray()
    {
        ContextMenu::fromArray([
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
    }
}
