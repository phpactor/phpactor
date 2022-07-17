<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests;

class LanguageServerCodeTransformExtensionTest extends IntegrationTestCase
{
    public function testServices(): void
    {
        $container = $this->container();

        foreach ($container->getServiceIds() as $serviceId) {
        }
        $this->addToAssertionCount(1);
    }
}
