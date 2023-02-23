<?php

namespace Phpactor\Extension\Core\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Configurator\ConfigManipulator;
use Phpactor\TestUtils\Workspace;

class ConfigManipulatorTest extends TestCase
{
    private Workspace $workspace;

    public function setUp(): void
    {
        $this->workspace = new Workspace(__DIR__ . '/../../Workspace');
        $this->workspace->reset();
    }

    public function testCreateNewConfig(): void
    {
        self::assertFileDoesNotExist($this->workspace->path('.phpactor.json'));

        (new ConfigManipulator(
            'path/to/json.schema',
            $this->workspace->path('.phpactor.json')
        ))->initialize();

        self::assertFileExists($this->workspace->path('.phpactor.json'));
        self::assertJson($this->workspace->getContents('.phpactor.json'));
    }
}
