<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Adapter\Twig;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Adapter\Twig\ClassShortNameResolver;

class ClassShortNameResolverTest extends TestCase
{
    /*
     * @testdox It returns the short name of the class
     */
    public function testResolver(): void
    {
        $resolver = new ClassShortNameResolver();
        $this->assertEquals(
            'TestPrototype.php.twig',
            $resolver->resolveName(new TestPrototype())
        );
    }
}

class TestPrototype extends Prototype
{
}
