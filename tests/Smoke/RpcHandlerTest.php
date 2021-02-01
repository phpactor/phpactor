<?php

namespace Phpactor\Tests\Smoke;

use Phpactor\Tests\IntegrationTestCase;

class RpcHandlerTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideName
     */
    public function testRpcCommandIsAvailable(string $name): void
    {
        $registry = $this->container()->get('rpc.handler_registry');
        $registry->get($name);
        $this->addToAssertionCount(1);
    }

    public function provideName()
    {
        yield [ 'cache_clear' ];
        yield [ 'class_inflect' ];
        yield [ 'class_new' ];
        yield [ 'class_search' ];
        yield [ 'complete' ];
        yield [ 'config' ];
        yield [ 'context_menu' ];
        yield [ 'copy_class' ];
        yield [ 'echo' ];
        yield [ 'extract_constant' ];
        yield [ 'extract_expression' ];
        yield [ 'extract_method' ];
        yield [ 'file_info' ];
        yield [ 'generate_accessor' ];
        yield [ 'generate_method' ];
        yield [ 'goto_definition' ];
        yield [ 'import_class' ];
        yield [ 'move_class' ];
        yield [ 'navigate' ];
        yield [ 'offset_info' ];
        yield [ 'override_method' ];
        yield [ 'references' ];
        yield [ 'rename_variable' ];
        yield [ 'status' ];
        yield [ 'transform' ];
        yield [ 'hover' ];
        yield [ 'change_visibility' ];
    }
}
