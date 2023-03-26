<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\SourceCodeLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\TextDocument\TextDocument;

class StringSourceLocatorTest extends TestCase
{
    public function testLocate(): void
    {
        $locator = new StringSourceLocator(TextDocument::fromString('Hello'));
        $source = $locator->locate(ClassName::fromString('Foobar'));

        $this->assertEquals('Hello', (string) $source);
    }
}
