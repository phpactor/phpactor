<?php

namespace Phpactor\Tests\Integration\Extension\Navigation\Navigator;

use Phpactor\ClassFileConverter\Adapter\Simple\SimpleClassToFile;
use Phpactor\Extension\Navigation\Navigator\WorseReflectionNavigator;
use Phpactor\Tests\IntegrationTestCase;
use Phpactor\WorseReflection\Bridge\Phpactor\ClassToFileSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseReflectionNavigatorTest extends IntegrationTestCase
{
    private readonly Reflector $reflector;

    public function testNavigateToParent(): void
    {
        $navigator = $this->create(
            <<<'EOT'
                // File:One.php
                <?php

                class One
                {
                }
                // File:Two.php
                <?php
                class Two extends One
                {
                }
                EOT
        );
        $destinations = $navigator->destinationsFor($this->workspaceDir() . '/Two.php');

        $this->assertEquals([
            'parent' => $this->workspaceDir() . '/One.php',
        ], $destinations);
    }

    public function testNavigateToInterfaces(): void
    {
        $navigator = $this->create(
            <<<'EOT'
                // File:One.php
                <?php

                interface One
                {
                }
                // File:Two.php
                <?php

                interface Two
                {
                }
                // File:Three.php
                <?php
                class Three implements One, Two
                {
                }
                EOT
        );
        $destinations = $navigator->destinationsFor($this->workspaceDir() . '/Three.php');

        $this->assertEquals([
            'interface:One' => $this->workspaceDir() . '/One.php',
            'interface:Two' => $this->workspaceDir() . '/Two.php',
        ], $destinations);
    }

    public function testNavigateFromInterfaceToParents(): void
    {
        $navigator = $this->create(
            <<<'EOT'
                // File:One.php
                <?php

                interface One
                {
                }
                // File:Two.php
                <?php

                interface Two
                {
                }
                // File:Three.php
                <?php
                interface Three extends One, Two
                {
                }
                EOT
        );
        $destinations = $navigator->destinationsFor($this->workspaceDir() . '/Three.php');

        $this->assertEquals([
            'interface:One' => $this->workspaceDir() . '/One.php',
            'interface:Two' => $this->workspaceDir() . '/Two.php',
        ], $destinations);
    }

    private function create(string $manifest): WorseReflectionNavigator
    {
        $workspace = $this->workspace()->create($this->workspaceDir());
        $workspace->reset();
        $workspace->loadManifest($manifest);
        $reflector = ReflectorBuilder::create()->addLocator(
            new ClassToFileSourceLocator(
                new SimpleClassToFile($this->workspaceDir())
            )
        )->build();

        return new WorseReflectionNavigator($reflector);
    }
}
