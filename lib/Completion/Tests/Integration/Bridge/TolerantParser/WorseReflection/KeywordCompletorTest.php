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
        yield 'method empty body keyword' => [
            '<?php class F { public function foo() { <> }}',
            [...$this->expectStatement()],
        ];
        yield 'method body keyword' => [
            '<?php class F { public function foo() { re<> }}',
            [...$this->expectStatement()],
        ];
        yield 'method body subnode' => [
            '<?php class F { public function foo() { if (true) { re<> } }}',
            [...$this->expectStatement()],
        ];
        yield 'root subnode' => [
            '<?php <>',
            [...$this->expectStatement()],
        ];
        yield 'namespace subnode' => [
            '<?php namespace X; <>',
            [...$this->expectStatement()],
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
    private function expectStatement(): Generator
    {
        $statements = [
            'do' => " {\n\t\$0\n} while (\$2);",
            'echo' => ' $1;$0',
            'for' => " (\${1:expr1}, \${2:expr2}, \${3:expr3}) {\n\$0\n}",
            'foreach' => " (\\\$\${1:expr} as \\\$\${2:key} => \\\$\${3:value}) {\$0\n}",
            'if' => " (\$1) {\$0\n}",
            'return' => ' $1;$0',
            'switch' => " (\\\$\${1:expr}) {\n\tcase \${2:expr}:\n\t\t\$0\n}",
            'try' => "  {\$3\n} catch (\${1:Exception} \\\$\${2:error}) {\$4\n}",
            'while' => " (\$1) {\$0\n}",
            'yield' => ' $1;$0',
        ];

        foreach ($statements as $name => $snippet) {
            yield ['name' => $name . ' ', 'snippet' => $name . $snippet];
        }
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
