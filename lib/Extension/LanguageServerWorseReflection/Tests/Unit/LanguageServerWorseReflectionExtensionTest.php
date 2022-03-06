<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Tests\Unit;

use Phpactor\Extension\LanguageServerWorseReflection\SourceLocator\WorkspaceSourceLocator;
use Phpactor\Extension\LanguageServerWorseReflection\Tests\IntegrationTestCase;

class LanguageServerWorseReflectionExtensionTest extends IntegrationTestCase
{
    public function testBoot(): void
    {
        $locator = $this->container()->get(WorkspaceSourceLocator::class);
        self::assertInstanceOf(WorkspaceSourceLocator::class, $locator);
    }
}
