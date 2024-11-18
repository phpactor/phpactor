<?php

declare(strict_types=1);

namespace Phpactor\Extension\Navigation\Tests\Application;

use Phpactor\Extension\Navigation\Tests\IntegrationTestCase;
use Phpactor\Extension\Navigation\Application\Navigator;
use Phpactor\Extension\Navigation\NavigationExtension;

class NavigatorTest extends IntegrationTestCase
{
    public function testProvidesCorrectDestination(): void
    {
        $navigator = $this->navigator();

        $this->workspace->put('src/Kernel.php', '<?php');
        $result = $navigator->destinationsFor($this->workspace->path('src/Kernel.php'));

        self::assertSame(['unit_test' => 'tests/Unit/KernelTest.php'], $result);
    }

    public function testCanCreate(): void
    {
        $navigator = $this->navigator();

        $this->workspace->put('src/Kernel.php', '<?php');
        $result = $navigator->canCreateNew($this->workspace->path('src/Kernel.php'), 'unit_test');

        self::assertTrue($result);
    }

    public function testNoNeedToCreate(): void
    {
        $navigator = $this->navigator();

        $this->workspace->put('src/Kernel.php', '<?php');
        $this->workspace->put('tests/Unit/KernelTest.php', '<?php');
        $result = $navigator->canCreateNew($this->workspace->path('src/Kernel.php'), 'unit_test');

        self::assertFalse($result);
    }

    /**
     * @param array<string,string> $destinations
     * @param array<string,string> $autocreate
     */
    private function navigator(
        array $destinations = ['source' => 'src/<kernel>.php', 'unit_test' => 'tests/Unit/<kernel>Test.php'],
        array $autocreate = ['source' => 'source', 'unit_test' => 'unit_test'],
    ): Navigator {
        $container = $this->container([
          NavigationExtension::PATH_FINDER_DESTINATIONS => $destinations,
          NavigationExtension::NAVIGATOR_AUTOCREATE  => $autocreate
        ]);
        /** @var Navigator */
        return $container->get('application.navigator');
    }
}
