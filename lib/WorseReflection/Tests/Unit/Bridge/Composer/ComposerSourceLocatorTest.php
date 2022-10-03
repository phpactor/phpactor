<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Composer;

use Phpactor\WorseReflection\Bridge\Composer\ComposerSourceLocator;
use Phpactor\WorseReflection\Core\Name;
use PHPUnit\Framework\TestCase;

class ComposerSourceLocatorTest extends TestCase
{
    public function testLocate(): void
    {
        $autoloader = require(__DIR__ . '/../../../../../../vendor/autoload.php');
        $locator = new ComposerSourceLocator($autoloader);
        $sourceCode = $locator->locate(Name::fromString(ComposerSourceLocatorTest::class));
        $this->assertEquals(__FILE__, realpath($sourceCode->path()));
    }
}
