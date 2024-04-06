<?php

namespace Phpactor\Indexer\Tests\Extension\Command;

use Generator;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

class IndexSearchCommandTest extends IntegrationTestCase
{
    /**
     * @param array<string> $args
     * @dataProvider provideQuery
     */
    public function testQueryIndex(array $args = []): void
    {
        $this->initProject();

        $process = new Process(array_merge([
            PHP_BINARY,
            __DIR__ . '/../../bin/console',
            'index:search',
        ], $args), $this->workspace()->path());
        $process->mustRun();
        self::assertEquals(0, $process->getExitCode());
    }

    /**
     * @return Generator<string, array{array<string>}>
     */
    public function provideQuery(): Generator
    {
        yield 'all' => [
            [
                '--limit=1'
            ]
        ];

        yield 'classes' => [
            [
                '--is-class',
                '--is-function',
                '--short-name=Foo',
                '--short-name-begins=Foo',
                '--fqn-begins=Foo',
                '--limit=1'
            ]
        ];

        yield 'constant' => [
            [
                '--is-constant',
            ]
        ];
    }
}
