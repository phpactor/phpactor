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
            '<?php class Foobar { public function __c<>',
            [...$this->expectMagicMethods()],
        ];
        yield '__construct 2' => [
            '<?php class Foo extends Bar implements One {    public function __c<> }',
            [...$this->expectMagicMethods()],
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
            $this->expect(['class ', 'enum ', 'function ', 'interface ', 'trait ']),
        ];
        yield 'class keyword 2' => [
            '<?php class F {} cl<>',
            $this->expect(['class ', 'enum ', 'function ', 'interface ', 'trait ']),
        ];
        yield 'class keyword 3' => [
            '<?php class F {function fo() {}} cl<>',
            $this->expect(['class ', 'enum ', 'function ', 'interface ', 'trait ']),
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

    /**
     * @return Generator<array{name:string,snippet:string}>
     */
    private function expectMagicMethods(): Generator
    {
        $methods = [
            '__construct' => "(\$1)\n{\$0\n}",
            '__call' => "(string \\\$\${1:name}, array \\\$\${2:arguments}): \${3:mixed}\n{\$0\n}",
            '__callStatic' => "(string \\\$\${1:name}, array \\\$\${2:arguments}): \${3:mixed}\n{\$0\n}",
            '__clone' => "(): void\n{\$0\n}",
            '__debugInfo' => "(): array\n{\$0\n}",
            '__destruct' => "(): void\n{\$0\n}",
            '__get' => "(string \\\$\${1:name}): \${3:mixed}\n{\$0\n}",
            '__invoke' => "(\$1): \${2:mixed}\n{\$0\n}",
            '__isset' => "(string \\\$\${1:name}): bool\n{\$0\n}",
            '__serialize' => "(): array\n{\$0\n}",
            '__set' => "(string \\\$\${1:name}, mixed \\\$\${2:value}): void\n{\$0\n}",
            '__set_state' => "(array \\\$\${1:properties}): object\n{\$0\n}",
            '__sleep' => "(): array\n{\$0\n}",
            '__toString' => "(): string\n{\$0\n}",
            '__unserialize' => "(array \\\$\${1:data}): void\n{\$0\n}",
            '__unset' => "(string \\\$\${1:name}): void\n{\$0\n}",
            '__wakeup' => "(): void\n{\$0\n}",
        ];

        foreach ($methods as $name => $snippet) {
            yield ['name' => $name . '(', 'snippet' => $name . $snippet];
        }
    }
}
