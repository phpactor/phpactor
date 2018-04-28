<?php

namespace Phpactor\Tests\Integration\Extension\Navigation\Navigator;

use PhpCsFixer\Tests\TestCase;
use Phpactor\ClassFileConverter\Adapter\Simple\SimpleClassToFile;
use Phpactor\Extension\Navigation\Navigator\WorseReflectionNavigator;
use Phpactor\Tests\IntegrationTestCase;
use Phpactor\WorseReflection\Bridge\Phpactor\ClassToFileSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseReflectionNavigatorTest extends IntegrationTestCase
{
    /**
     * @var Reflector
     */
    private $reflector;

    public function testNavigateToParent()
    {
        $navigator = $this->create();
        $destinations = $navigator->destinationsFor($this->workspaceDir() . '/Two.php');

        $this->assertEquals([
            'parent (One)' => $this->workspaceDir() . '/One.php',
        ], $destinations);
    }

    private function create(): WorseReflectionNavigator
    {
        $workspace = $this->workspace()->create($this->workspaceDir());
        $workspace->reset();
        $workspace->loadManifest(<<<'EOT'
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
        $reflector = ReflectorBuilder::create()->addLocator(
            new ClassToFileSourceLocator(
                new SimpleClassToFile($this->workspaceDir())
            )
        )->build();

        return new WorseReflectionNavigator($reflector);
    }
}
