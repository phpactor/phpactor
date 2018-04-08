<?php

namespace Phpactor\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Phpactor\Container\PhpactorContainer;
use stdClass;
use Phpactor\Container\Container;

class PhpactorContainerTest extends TestCase
{
    /**
     * @var PhpactorContainer
     */
    private $container;

    public function setUp()
    {
        $this->container = new PhpactorContainer([
            'configKey1' => 'value1',
        ]);
    }

    public function testThrowsExceptionForUnknownService()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No service with ID "foobar" exists');

        $this->container->get('foobar');
    }

    public function testRetrievesService()
    {
        $this->container->register('foobar', function (Container $container) {
            return new stdClass();
        });

        $service = $this->container->get('foobar');
        $this->assertInstanceOf(stdClass::class, $service);
    }

    public function testRetrievesSameServiceWhenCalledMultipleTimes()
    {
        $this->container->register('foobar', function (Container $container) {
            return new stdClass();
        });

        $service1 = $this->container->get('foobar');
        $service2 = $this->container->get('foobar');
        $this->assertSame($service1, $service2);
    }

    public function testSaysIfItHasAService()
    {
        $this->container->register('foobar', function (Container $container) {
            return new stdClass();
        });

        $this->assertTrue($this->container->has('foobar'));
        $this->assertFalse($this->container->has('barfoo'));
    }

    public function testReturnsServicesByTag()
    {
        $this->container->register('foobar', function (Container $container) {
            return new stdClass();
        }, [ 'foobar' => []]);

        $serviceIds = $this->container->getServiceIdsForTag('foobar');
        $this->assertCount(1, $serviceIds);

        $this->assertEquals([
            'foobar' => []
        ], $serviceIds);
    }

    public function testThrowsExceptionIfParmaeterNotFound()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown parameter "foobar", known parameters');
        $this->container->getParameter('foobar');
    }

    public function testReturnsParameter()
    {
        $result = $this->container->getParameter('configKey1');
        $this->assertEquals('value1', $result);
    }

    public function testBuildReturnsAConfiguredContainer()
    {
        $container = $this->container->build([
            'hello' => 'goodbye'
        ]);

        $this->assertInstanceOf(Container::class, $container);
        $this->assertEquals('goodbye', $container->getParameter('hello'));
    }
}
