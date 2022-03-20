<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use Closure;
use Phpactor\WorseReflection\Core\Exception\FunctionNotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class FunctionReflectorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectFunction
     */
    public function testReflectFunction(string $source, string $name, Closure $assertion): void
    {
        $reflection = $this->createReflector($source)->reflectFunction($name);
        $assertion($reflection);
    }

    public function provideReflectFunction()
    {
        yield 'reflect function' => [
            '<?php function hello() {}',
            'hello',
            function (ReflectionFunction $function): void {
                $this->assertEquals('hello', $function->name());
            }
        ];
    }

    public function testThrowsExceptionIfFunctionNotFound(): void
    {
        $this->expectException(FunctionNotFound::class);
        $this->createReflector('<?php ')->reflectFunction('hallo');
    }
}
