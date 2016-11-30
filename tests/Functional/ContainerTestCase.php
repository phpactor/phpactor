<?php

namespace Phpactor\Tests\Functional;

use PhpBench\DependencyInjection\Container;
use Phpactor\Extension\CoreExtension;

class ContainerTestCase extends \PHPUnit_Framework_TestCase
{
    private $container;

    public function getContainer(array $config = [])
    {
        if ($this->container) {
            return $this->container;
        }

        $this->container = new Container([
            CoreExtension::class,
        ], $config);
        $this->container->init();

        return $this->container;
    }
}
