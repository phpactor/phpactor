<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Tests\Integration\IntegrationTestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class InterfaceFormatterTest extends IntegrationTestCase
{
    public function testFormatsInterface(): void
    {
        $interface = ReflectorBuilder::create()->build()->reflectClassLikesIn(TextDocumentBuilder::fromUnknown('<?php namespace Bar {interface Foobar {}}'))->first();
        self::assertTrue($this->formatter()->canFormat($interface));
        self::assertEquals('Bar\\Foobar (interface)', $this->formatter()->format($interface));
    }
}
