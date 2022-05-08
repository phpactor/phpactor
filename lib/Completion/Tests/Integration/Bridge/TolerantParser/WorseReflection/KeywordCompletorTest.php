<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\TokenStringMaps;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\KeywordCompletor;
use Phpactor\Completion\Core\Suggestion;
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
            '<?php class Foobar { <>',
            $this->expect(['private', 'protected', 'public']),
        ];

        yield 'member keyword postfix' => [
            '<?php class Foobar { private <>',
            $this->expect(['const', 'function']),
        ];

        yield 'class implements 1' => [
            '<?php class Foobar <>',
            $this->expect(['implements', 'extends']),
        ];
        yield 'class implements 2' => [
            '<?php class Foobar impl<>',
            $this->expect(['implements', 'extends']),
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
