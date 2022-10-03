<?php

namespace Phpactor\CodeBuilder\Tests;

use Generator;
use Phpactor\TestUtils\Workspace;
use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class IntegrationTestCase extends TestCase
{
    public function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }

    protected function yieldExamplesIn(string $path): Generator
    {
        if (!file_exists($path)) {
            throw new RuntimeException(sprintf(
                'Directory "%s" does not exist',
                $path
            ));
        }


        foreach (glob($path . '/*.test.php') as $filename) {
            yield basename($filename) => [
                $filename
            ];
        }
    }
}
