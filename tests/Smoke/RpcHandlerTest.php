<?php

namespace Phpactor\Tests\Smoke;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Extension\Rpc\HandlerRegistry;
use Phpactor\Tests\IntegrationTestCase;

class RpcHandlerTest extends IntegrationTestCase
{
    #[DataProvider('provideName')]
    public function testRpcCommandIsAvailable(string $name): void
    {
        $registry = $this->container()->expect('rpc.handler_registry', HandlerRegistry::class);
        $registry->get($name);
        $this->addToAssertionCount(1);
    }

    /**
     * @return Generator<array{string}>
     */
    public static function provideName(): Generator
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
