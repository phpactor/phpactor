<?php

namespace Phpactor\Tests\Unit\Extension\LanguageServer\Server;

use DTL\ArgumentResolver\ArgumentResolver;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\Server\Dispatcher;
use Phpactor\Extension\LanguageServer\Server\Method;
use Phpactor\Extension\LanguageServer\Server\MethodRegistry;

class DispatcherTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */

    private $argumentResolver;

    /**
     * @var ObjectProphecy
     */
    private $methodRegistry;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function setUp()
    {
        $this->argumentResolver = $this->prophesize(ArgumentResolver::class);
        $this->methodRegistry = $this->prophesize(MethodRegistry::class);

        $this->dispatcher = new Dispatcher(
            $this->methodRegistry->reveal(),
            $this->argumentResolver->reveal()
        );
    }

    public function testDispatch()
    {
        $arguments = [ 'one', 'two' ];
        $method = new class implements Method {
            public function name(): string { 
                return 'methodOne'; 
            }
            public function __invoke($one, $two) { return 'return_value'; }
        };
        $this->methodRegistry->get('methodOne')->willReturn($method);

        $this->argumentResolver->resolveArguments(get_class($method), '__invoke', $arguments)->willReturn($arguments);

        $result = $this->dispatcher->dispatch('methodOne', $arguments);
        $this->assertEquals('return_value', $result);
    }

}
