<?php

namespace Phpactor\Tests\Unit\Extension\LanguageServer\Server;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\Exception\UnknownMethod;
use Phpactor\Extension\LanguageServer\Server\Method;
use Phpactor\Extension\LanguageServer\Server\MethodRegistry;

class MethodRegistryTest extends TestCase
{
    public function testThrowsExceptionOnUnknownMethod()
    {
        $this->expectException(UnknownMethod::class);
        $this->expectExceptionMessage('Method "methodOne" is not known');
        $methodRegistry = $this->create([]);
        $methodRegistry->get('methodOne');
    }

    public function testReturnsMethod()
    {
        $method = new class implements Method {
            function name(): string { return 'methodOne'; }
    };

        $registry = $this->create([ $method ]);

        $result = $registry->get('methodOne');
        $this->assertSame($method, $result);
    }

    private function create(array $array)
    {
        return new MethodRegistry($array);
    }
}
