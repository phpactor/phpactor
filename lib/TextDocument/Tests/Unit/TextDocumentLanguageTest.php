<?php

namespace Phpactor\TextDocument\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentLanguage;

class TextDocumentLanguageTest extends TestCase
{
    public function testCreate(): void
    {
        $language = TextDocumentLanguage::fromString('php');
        $this->assertEquals('php', (string) $language);
        $this->assertTrue($language->isDefined());
        $this->assertTrue($language->isPhp());
        $this->assertTrue($language->is('php'));
        $this->assertTrue($language->is('PHP'));
        $this->assertFalse($language->is('french'));
        $this->assertTrue($language->in(['php', 'cobol']));
        $this->assertFalse($language->in(['c', 'cobol']));
    }

    public function testCreateUndefined(): void
    {
        $language = TextDocumentLanguage::undefined();
        $this->assertFalse($language->isDefined());
    }
}
