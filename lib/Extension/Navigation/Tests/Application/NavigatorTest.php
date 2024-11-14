<?php

declare(strict_types=1);

namespace Phpactor\Extension\Navigation\Tests\Application;

use Phpactor\Extension\Navigation\Tests\IntegrationTestCase;
use Phpactor\Extension\Navigation\NavigationExtension;
use Phpactor\Extension\Navigation\Application\Navigator;

class NavigatorTest extends IntegrationTestCase
{
    public function testSomething(): void
    {
        $navigator = $this->navigator([
            NavigationExtension::NAVIGATOR_AUTOCREATE => [
              'source' => 'source',
              'unit_test' => 'unit_test'
            ],
            NavigationExtension::PATH_FINDER_DESTINATIONS => [
              'source' => 'src/<kernel>.php',
              'unit_test' => 'tests/Unit/<kernel>Test.php'
            ]
        ]);

        $this->workspace->put('src/Kernel.php', '<?php');
        $result = $navigator->destinationsFor($this->workspace->path('src/Kernel.php'));

        self::assertSame(['unit_test' => 'tests/Unit/KernelTest.php'], $result);
    }

    public function testCanCreate(): void
    {
        $navigator = $this->navigator([
            NavigationExtension::NAVIGATOR_AUTOCREATE => [
              'source' => 'source',
              'unit_test' => 'unit_test'
            ],
            NavigationExtension::PATH_FINDER_DESTINATIONS => [
              'source' => 'src/<kernel>.php',
              'unit_test' => 'tests/Unit/<kernel>Test.php'
            ]
        ]);

        $this->workspace->put('src/Kernel.php', '<?php');
        $result = $navigator->canCreateNew($this->workspace->path('src/Kernel.php'), 'unit_test');

        self::assertTrue($result);
    }

    public function testNoNeedToCreate(): void
    {
        $navigator = $this->navigator([
            NavigationExtension::NAVIGATOR_AUTOCREATE => [
              'source' => 'source',
              'unit_test' => 'unit_test'
            ],
            NavigationExtension::PATH_FINDER_DESTINATIONS => [
              'source' => 'src/<kernel>.php',
              'unit_test' => 'tests/Unit/<kernel>Test.php'
            ]
        ]);

        $this->workspace->put('src/Kernel.php', '<?php');
        $this->workspace->put('tests/Unit/KernelTest.php', '<?php');
        $result = $navigator->canCreateNew($this->workspace->path('src/Kernel.php'), 'unit_test');

        self::assertFalse($result);
    }

    /**
     * @param array{
     * 'navigator.destinations': array<string, string>,
     * 'navigator.autocreate': array<string, string>,
     * } $config
     */
    private function navigator(array $config): Navigator
    {
        $container = $this->container($config);
        /** @var Navigator */
        return $container->get('application.navigator');
    }
}
