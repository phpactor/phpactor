<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\SourceCodeLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;

class StringSourceLocatorTest extends TestCase
{
    public function testLocate(): void
    {
        $locator = new StringSourceLocator(SourceCode::fromString('Hello'));
        $source = $locator->locate(ClassName::fromString('Foobar'));

        $this->assertEquals('Hello', (string) $source);
    }
}
