<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\Method;

class MethodTest extends TestCase
{
    /**
     * @testfox It returns if it is static or abstract
     */
    public function testAbstractStatic(): void
    {
        $method = $this->createMethodModifier(Method::IS_STATIC);
        $this->assertTrue($method->isStatic());
        $this->assertFalse($method->isAbstract());

        $method = $this->createMethodModifier(Method::IS_ABSTRACT);
        $this->assertTrue($method->isAbstract());
        $this->assertFalse($method->isStatic());

        $method = $this->createMethodModifier(Method::IS_ABSTRACT|Method::IS_STATIC);
        $this->assertTrue($method->isAbstract());
        $this->assertTrue($method->isStatic());
    }

    private function createMethodModifier(int $modifier): Method
    {
        return new Method('test', modifierFlags: $modifier);
    }
}
