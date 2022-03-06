<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\ReflectorBuilder;

class InterfaceFormatterTest extends IntegrationTestCase
{
    public function testFormatsInterface(): void
    {
        $interface = ReflectorBuilder::create()->build()->reflectClassesIn('<?php namespace Bar {interface Foobar {}}')->first();
        self::assertTrue($this->formatter()->canFormat($interface));
        self::assertEquals('Bar\\Foobar (interface)', $this->formatter()->format($interface));
    }
}
