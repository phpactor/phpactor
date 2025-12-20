<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser;

use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantUpdater;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Tests\Adapter\UpdaterTestCase;

class TolerantUpdaterTest extends UpdaterTestCase
{
    protected function updater(): Updater
    {
        return new TolerantUpdater(new TwigRenderer());
    }
}
