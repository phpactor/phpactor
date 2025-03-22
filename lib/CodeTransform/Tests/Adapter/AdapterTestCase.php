<?php

namespace Phpactor\CodeTransform\Tests\Adapter;

use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantUpdater;
use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;
use Phpactor\TestUtils\ExtractOffset;
use Symfony\Component\Filesystem\Path;

class AdapterTestCase extends TestCase
{
    protected function renderer()
    {
        return new TwigRenderer();
    }

    protected function updater()
    {
        return new TolerantUpdater($this->renderer());
    }

    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/../Workspace');
    }

    protected function sourceExpected($manifestPath)
    {
        $workspace = $this->workspace();
        $workspace->reset();

        $manifestPath = Path::canonicalize($manifestPath);
        if (!file_exists($manifestPath)) {
            touch($manifestPath);
        }

        $workspace->loadManifest(file_get_contents($manifestPath));
        $source = $workspace->getContents('source');
        $expected = $workspace->getContents('expected');

        return [ $source, $expected ];
    }

    protected function sourceExpectedAndOffset($manifestPath)
    {
        [$source, $expected] = $this->sourceExpected($manifestPath);
        [$source, $offsetStart, $offsetEnd] = ExtractOffset::fromSource($source);

        return [ $source, $expected, $offsetStart, $offsetEnd ];
    }
}
