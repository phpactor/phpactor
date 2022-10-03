<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\Composer;

use Phpactor\WorseReflection\Bridge\Composer\ComposerSourceLocator;
use Phpactor\WorseReflection\Core\Name;
use PHPUnit\Framework\TestCase;

class ComposerSourceCodeLocatorTest extends TestCase
{
    public function testLocateSource(): void
    {
        $classLoader = require __DIR__ . '/../../../../../../vendor/autoload.php';
        $locator = new ComposerSourceLocator($classLoader);
        $code = $locator->locate(Name::fromString(__CLASS__));
        $this->assertSame(file_get_contents(__FILE__), (string) $code);
    }
}
