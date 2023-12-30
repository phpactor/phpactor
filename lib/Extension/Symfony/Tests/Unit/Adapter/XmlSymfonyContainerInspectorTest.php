<?php

namespace Phpactor\Extension\Symfony\Tests\Unit\Adapter;

use Phpactor\Extension\Symfony\Adapter\Symfony\XmlSymfonyContainerInspector;
use Phpactor\Extension\Symfony\Model\SymfonyContainerParameter;
use Phpactor\Extension\Symfony\Model\SymfonyContainerService;
use Phpactor\Extension\Symfony\Tests\IntegrationTestCase;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;

class XmlSymfonyContainerInspectorTest extends IntegrationTestCase
{
    public function testNotFound(): void
    {
        self::assertEquals([], $this->inspect($this->workspace()->path('services.xml'))->services());
    }

    public function testListsServicesFormValidXml(): void
    {
        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
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

    public function testListsPublicServicesOnly(): void
    {
        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
                <?xml version="1.0" encoding="utf-8"?>
                <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
                  <services>
                    <service id="one" class="One" public="true" synthetic="true"/>
                    <service id="two" class="Two" public="false" synthetic="true"/>
                    <service id="two" class="Three" />
                  </services>
                </container>
                EOT
        );
        self::assertEquals([
            new SymfonyContainerService('one', TypeFactory::class('One')),
        ], $this->inspect($this->workspace()->path('services.xml'))->services());
    }

    public function testNoServices(): void
    {
        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
                <?xml version="1.0" encoding="utf-8"?>
                <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
                </container>
                EOT
        );
        self::assertEquals([
        ], $this->inspect($this->workspace()->path('services.xml'))->services());
    }

    public function testRetrieveService(): void
    {
        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
                <?xml version="1.0" encoding="utf-8"?>
                <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
                    <services>
                        <service id="service_container" class="Symfony\Component\DependencyInjection\ContainerInterface" public="true" synthetic="true"/>
                    </services>
                </container>
                EOT
        );
        self::assertEquals(
            new SymfonyContainerService(
                'service_container',
                TypeFactory::class('Symfony\Component\DependencyInjection\ContainerInterface')
            ),
            $this->inspect($this->workspace()->path('services.xml'))->service('service_container')
        );
    }

    public function testRetrieveNonPublicServiceIfConfigured(): void
    {
        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
                <?xml version="1.0" encoding="utf-8"?>
                <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
                    <services>
                        <service id="App\Component\Foo\Service\RequestHandlerService" class="App\Component\Foo\Service\RequestHandlerService" autowire="true" autoconfigure="true" public="true">
                        </service>
                    </services>
                </container>
                EOT
        );
        self::assertEquals(
            new SymfonyContainerService(
                'App\Component\Foo\Service\RequestHandlerService',
                TypeFactory::class('App\Component\Foo\Service\RequestHandlerService')
            ),
            $this->inspect(
                $this->workspace()->path('services.xml'),
                publicOnly: false,
            )->service('App\Component\Foo\Service\RequestHandlerService')
        );
    }

    public function testDefinitionWithNoAttributes(): void
    {
        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
                <?xml version="1.0" encoding="utf-8"?>
                <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
                  <services>
                    <service/>
                  </services>
                </container>
                EOT
        );
        self::assertEquals([
        ], $this->inspect($this->workspace()->path('services.xml'))->services());
    }

    public function testParameters(): void
    {
        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
                <?xml version="1.0" encoding="utf-8"?>
                <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
                  <parameters>
                    <parameter key="kernel.project_dir">/app</parameter>
                    <parameter key="kernel.environment">dev</parameter>
                  </parameters>
                </container>
                EOT
        );
        self::assertEquals([
            new SymfonyContainerParameter('kernel.project_dir', new StringLiteralType('/app')),
            new SymfonyContainerParameter('kernel.environment', new StringLiteralType('dev')),
        ], $this->inspect($this->workspace()->path('services.xml'))->parameters());
    }

    public function testCachesResultIfMtimeSame(): void
    {
        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
                <?xml version="1.0" encoding="utf-8"?>
                <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
                  <services>
                    <service id="test" class="Foo" public="true"/>
                  </services>
                </container>
                EOT
        );
        touch($this->workspace()->path('services.xml'), 100);
        $inspector = $this->inspect($this->workspace()->path('services.xml'));
        self::assertEquals('Foo', $inspector->service('test')->type->__toString());

        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
                <?xml version="1.0" encoding="utf-8"?>
                <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
                  <services>
                    <service id="bar" class="Foo" public="true"/>
                  </services>
                </container>
                EOT
        );
        touch($this->workspace()->path('services.xml'), 100);
        self::assertNotNull($inspector->service('test'));
    }

    public function testUsesCachedResultIfMtimeDifferent(): void
    {
        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
                <?xml version="1.0" encoding="utf-8"?>
                <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
                  <services>
                    <service id="test" class="Foo" public="true"/>
                  </services>
                </container>
                EOT
        );
        touch($this->workspace()->path('services.xml'), 100);
        $inspector = $this->inspect($this->workspace()->path('services.xml'));
        self::assertEquals('Foo', $inspector->service('test')->type->__toString());

        $this->workspace()->put(
            'services.xml',
            <<<'EOT'
                <?xml version="1.0" encoding="utf-8"?>
                <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
                  <services>
                    <service id="bar" class="Foo" public="true"/>
                  </services>
                </container>
                EOT
        );
        touch($this->workspace()->path('services.xml'), 101);
        self::assertNull($inspector->service('test'));
    }

    private function inspect(string $xmlPath, bool $publicOnly = true): XmlSymfonyContainerInspector
    {
        return new XmlSymfonyContainerInspector($xmlPath, $publicOnly);
    }
}
