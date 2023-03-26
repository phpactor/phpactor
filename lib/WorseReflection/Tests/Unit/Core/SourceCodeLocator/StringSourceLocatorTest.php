<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\SourceCodeLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;

class StringSourceLocatorTest extends TestCase
{
    public function testLocate(): void
    {
        $locator = new StringSourceLocator(TextDocumentBuilder::create('Hello')->build());
        $source = $locator->locate(ClassName::fromString('Foobar'));

        $this->assertEquals('Hello', (string) $source);
    }
}
