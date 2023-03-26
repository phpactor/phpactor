<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Tests\Integration\IntegrationTestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class TraitFormatterTest extends IntegrationTestCase
{
    public function testFormatsTrait(): void
    {
        $trait = ReflectorBuilder::create()->build()->reflectClassesIn(TextDocumentBuilder::fromUnknown('<?php namespace Bar {trait Foobar {}}'))->first();
        self::assertTrue($this->formatter()->canFormat($trait));
        self::assertEquals('Bar\\Foobar (trait)', $this->formatter()->format($trait));
    }

    public function testFormatsDeprecatedTrait(): void
    {
        $trait = ReflectorBuilder::create()->build()->reflectClassesIn(TextDocumentBuilder::fromUnknown('<?php namespace Bar {/** @deprecated */trait Foobar {}}'))->first();
        self::assertTrue($this->formatter()->canFormat($trait));
        self::assertEquals('⚠ Bar\\Foobar (trait)', $this->formatter()->format($trait));
    }
}
