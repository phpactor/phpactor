<?php

namespace Phpactor\Configurator\Tests\Unit\Config;

use Phpactor\Configurator\Config\JsonConfig;
use Phpactor\Configurator\Tests\IntegrationTestCase;

class JsonConfigTest extends IntegrationTestCase
{
    public function testLoadsJsonConfig(): void
    {
        $this->workspace()->put('foo.json', '{"foo": "bar"}');
        $config = JsonConfig::fromPath($this->workspace()->path('foo.json'));
        self::assertFalse($config->has('bar'));
        self::assertTrue($config->has('foo'));
    }
}
