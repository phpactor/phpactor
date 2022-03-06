<?php

namespace Phpactor\Extension\Debug\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Debug\Model\ConfigInitializer;
use Phpactor\TestUtils\Workspace;

class ConfigInitializerTest extends TestCase
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function setUp(): void
    {
        $this->workspace = new Workspace(__DIR__ . '/../../Workspace');
        $this->workspace->reset();
    }

    public function testCreateNewConfig(): void
    {
        self::assertFileDoesNotExist($this->workspace->path('.phpactor.json'));

        (new ConfigInitializer(
            'path/to/json.schema',
            $this->workspace->path('.phpactor.json')
        ))->initialize();

        self::assertFileExists($this->workspace->path('.phpactor.json'));
        self::assertJson($this->workspace->getContents('.phpactor.json'));
    }
}
