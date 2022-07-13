<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Generator;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\KeywordCompletor;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\TextDocument\TextDocument;

class KeywordCompletorTest extends TolerantCompletorTestCase
{
    /**
     * @dataProvider provideComplete
     * @param array{string,array<string,mixed>[]} $expected
     */
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    /**
     * @return Generator<string,array{string,array<string,mixed>[]}>
     */
    public function provideComplete(): Generator
    {
        yield 'member keywords' => [
            '<?php class Foobar { p<>',
            $this->expect(['private ', 'protected ', 'public ']),
        ];

        yield 'member keyword postfix' => [
            '<?php class Foobar { private <>',
            $this->expect(['const ', 'function ']),
        ];
        yield 'member keyword postfix 2' => [
            '<?php class Foobar { private func<>',
            $this->expect(['const ', 'function ']),
        ];

        yield '__construct' => [
            '<?php class Foobar { public function __<>',
            $this->expect(['__construct(']),
        ];
        yield '__construct 2' => [
            '<?php class Foo extends Bar implements One {    public function __<> }',
            $this->expect(['__construct(']),
        ];


        yield 'class implements 1' => [
            '<?php class Foobar <>',
            $this->expect(['extends ', 'implements ']),
        ];
        yield 'class implements 2' => [
            '<?php class Foobar impl<>',
            $this->expect(['extends ', 'implements ']),
        ];

        yield 'class keyword' => [
            '<?php cl<>',
            $this->expect(['class ', 'function ', 'interface ', 'trait ']),
        ];
        yield 'class keyword 2' => [
            '<?php class F {} cl<>',
            $this->expect(['class ', 'function ', 'interface ', 'trait ']),
        ];
        yield 'class keyword 3' => [
            '<?php class F {function fo() {}} cl<>',
            $this->expect(['class ', 'function ', 'interface ', 'trait ']),
        ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        return new KeywordCompletor();
    }

    /**
     * @return array<array<string,mixed>>
     * @param array<string> $array
     */
    private function expect(array $array): array
    {
        return array_map(fn (string $keyword) => [
            'name' => $keyword,
        ], $array);
    }
}
