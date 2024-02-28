<?php

namespace Phpactor\Indexer\Tests\Extension\Command;

use Generator;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

class IndexQueryCommandTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideQuery
     */
    public function testQueryIndex(string $query): void
    {
        $this->initProject();

        $process = new Process([
            PHP_BINARY,
            __DIR__ . '/../../bin/console',
            'index:query',
            $query
        ], $this->workspace()->path());
        $process->mustRun();
        self::assertEquals(0, $process->getExitCode());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideQuery(): Generator
    {
        yield 'method' => [
            'method#testQueryIndex',
        ];
        yield 'constant' => [
            'constant#RECORD_TYPE',
        ];
        yield 'property' => [
            'property#workspace',
        ];
        yield 'class' => [
            __CLASS__,
        ];
        yield 'sprintf' => [
            'sprintf',
        ];
    }
}
