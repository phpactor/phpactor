<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Composer;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\Composer\ComposerSourceLocator;
use Phpactor\WorseReflection\Core\Name;
use Symfony\Component\Filesystem\Path;

class ComposerSourceLocatorTest extends TestCase
{
    public function testLocate(): void
    {
        $autoloader = require(__DIR__ . '/../../../../../../vendor/autoload.php');
        $locator = new ComposerSourceLocator($autoloader);
        $sourceCode = $locator->locate(Name::fromString(ComposerSourceLocatorTest::class));
        $this->assertEquals(Path::canonicalize(__FILE__), $sourceCode->uri()->path());
    }
}
