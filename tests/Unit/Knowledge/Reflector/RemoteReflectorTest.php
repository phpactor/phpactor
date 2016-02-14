<?php

namespace PhpActor\Tests\Unit\Knowledge\Reflector;

use PhpActor\Knowledge\Reflector\RemoteReflector;
use PhpActor\Knowledge\Reflection\ClassReflection;

class RemoteReflectorTest extends \PHPUnit_Framework_TestCase
{
    private $reflector;

    public function setUp()
    {
        $this->reflector = new RemoteReflector();
    }

    /**
     * It should reflect the named file and return a reflection class.
     */
    public function testReflect()
    {
        $hierarchy = $this->reflector->reflect(
            __DIR__ . '/examples/Class3.php',
            $this->getBootstrap()
        );

        $classes = $hierarchy->getClasses();
        $this->assertCount(3, $classes);

        $this->assertInstanceOf(ClassReflection::class, $classes[0]);
        $this->assertEquals('Class1', $classes[0]->getShortName());
        $this->assertEquals('Class2', $classes[1]->getShortName());
        $this->assertEquals('Class3', $classes[2]->getShortName());

        $methods = $hierarchy->getMethods();
        $this->assertEquals(array(
            'noParams',
            'simpleParams',
            'getZad',
            'getBoz',
        ), array_keys($methods));

        $params = $methods['simpleParams']->getParams();
        $this->assertEquals(array(
            'param',
            'poo',
        ), array_keys($params));
    }

    private function getBootstrap()
    {
        return __DIR__ . '/../../../../vendor/autoload.php';
    }
}
