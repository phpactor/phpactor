<?php

namespace Phpactor\Tests\Unit\Rpc;

use PHPUnit\Framework\TestCase;
use Phpactor\Tests\Unit\Rpc\ActionRegistryTest;
use Phpactor\Rpc\Action;

class ActionRegistryTest extends TestCase
{
    public function testExceptionForUnkown()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Do');
        $action = $this->prophesize(Action::class);
        $action->name()->willReturn('one');
        $registry = $this->create([ $action->reveal() ]);

        $registry->get('aaa');
    }

    public function create(array $actions = [])
    {
        return new ActionRegistryTest($actions);
    }
}
