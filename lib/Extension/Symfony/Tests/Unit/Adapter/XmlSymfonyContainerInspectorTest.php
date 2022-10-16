<?php

namespace Phpactor\Extension\Symfony\Tests\Unit\Adapter;

use Phpactor\Extension\Symfony\Adapter\Symfony\XmlSymfonyContainerInspector;
use Phpactor\Extension\Symfony\Model\SymfonyContainerService;
use Phpactor\Extension\Symfony\Tests\IntegrationTestCase;
use Phpactor\WorseReflection\Core\TypeFactory;

class XmlSymfonyContainerInspectorTest extends IntegrationTestCase
{
    public function testNotFound(): void
    {
        self::assertEquals([], $this->inspect($this->workspace()->path('services.xml'))->services());
    }

    public function testListsServices(): void
    {
        $this->workspace()->put('services.xml', <<<'EOT'
            <?xml version="1.0" encoding="utf-8"?>
            <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
              <services>
                <service id="service_container" class="Symfony\Component\DependencyInjection\ContainerInterface" public="true" synthetic="true"/>
              </services>
            </container>
            EOT
        );
        self::assertEquals([
            new SymfonyContainerService('service_container', TypeFactory::class('Symfony\Component\DependencyInjection\ContainerInterface')),
        ], $this->inspect($this->workspace()->path('services.xml'))->services());
    }

    private function inspect(string $xmlPath): XmlSymfonyContainerInspector
    {
        return new XmlSymfonyContainerInspector($xmlPath);
    }
}
