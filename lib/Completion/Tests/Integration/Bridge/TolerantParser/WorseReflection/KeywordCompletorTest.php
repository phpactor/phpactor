<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\KeywordCompletor;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\TextDocument\TextDocument;

class KeywordCompletorTest extends TolerantCompletorTestCase
{
    /**
     * @param array{string,array<string,mixed>[]} $expected
     */
    #[DataProvider('provideComplete')]
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    /**
     * @return Generator<string,array{string,array<string,mixed>[]}>
     */
    public static function provideComplete(): Generator
    {
        yield 'member keywords' => [
            '<?php class Foobar { p<>',
            self::expect(['private ', 'protected ', 'public ']),
        ];

        yield 'member keyword postfix' => [
            '<?php class Foobar { private <>',
            self::expect(['const ', 'function ']),
        ];
        yield 'member keyword postfix 2' => [
            '<?php class Foobar { private func<>',
            self::expect(['const ', 'function ']),
        ];

        yield '__construct' => [
            '<?php class Foobar { public function __c<>',
            [...self::expectMagicMethods()],
        ];
        yield '__construct 2' => [
            '<?php class Foo extends Bar implements One {    public function __c<> }',
            [...self::expectMagicMethods()],
        ];

        yield 'no magic methods here' => [
            '<?php class Foobar { public function x(<>)',
            [],
        ];

        yield 'class implements 1' => [
            '<?php class Foobar <>',
            self::expect(['extends ', 'implements ']),
        ];
        yield 'class implements 2' => [
            '<?php class Foobar impl<>',
            self::expect(['extends ', 'implements ']),
        ];

        yield 'class keyword' => [
            '<?php cl<>',
            self::expect(['class ', 'enum ', 'function ', 'interface ', 'trait ']),
        ];
        yield 'class keyword 2' => [
            '<?php class F {} cl<>',
            self::expect(['class ', 'enum ', 'function ', 'interface ', 'trait ']),
        ];
        yield 'class keyword 3' => [
            '<?php class F {function fo() {}} cl<>',
            self::expect(['class ', 'enum ', 'function ', 'interface ', 'trait ']),
        ];
        yield 'match keyword' => [
            '<?php class F { public function foo() { $x = mat<> }}',
            [...self::expectExpressions()],
        ];
        yield 'match unexpected' => [
            '<?php class F { public function foo() { $this->mat<> }}',
            [],
        ];
        yield 'match unexpected 2' => [
            '<?php class F { public function foo() { $this->foo(<>) }}',
            [...self::expectExpressions()],
        ];
        yield 'match unexpected 3' => [
            '<?php class F { public function foo() { $this->foo(self::<>) }}',
            [],
        ];
        yield 'match unexpected 4' => [
            '<?php if (1)<> {}',
            [],
        ];
        yield 'match unexpected in string' => [
            '<?php strlen(\'<>',
            [],
        ];
        yield 'match unexpected 5' => [
            '<?php $<>',
            [],
        ];

        yield 'if condition classes' => [
            '<?php class Stuff { public function testing() { if ($this i<>} }',
            self::expect(['instanceof ']),
        ];
        yield 'if condition' => [
            '<?php if ($test i<>',
            self::expect(['instanceof ']),
        ];
        yield 'while with empty expression' => [
            '<?php while (<>',
            self::expect([]),
        ];
        yield 'while condition' => [
            '<?php while ($test i<>',
            self::expect(['instanceof ']),
        ];
        yield 'while condition (without variable)' => [
            '<?php while (<>',
            [],
        ];
        yield 'while condition (with expression)' => [
            '<?php while ($node->getParent() i<>',
            self::expect(['instanceof ']),
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
    private static function expect(array $array): array
    {
        return array_map(fn (string $keyword) => [
            'name' => $keyword,
        ], $array);
    }

    /**
     * @return Generator<array{name:string,snippet:string}>
     */
    private static function expectExpressions(): Generator
    {
        $expressions = [
            'match' => " (\$1) {\$0\n}",
            'throw' => ' $1',
        ];

        foreach ($expressions as $name => $snippet) {
            yield ['name' => $name . ' ', 'snippet' => $name . $snippet];
        }
    }

    /**
     * @return Generator<array{name:string,snippet:string}>
     */
    private static function expectMagicMethods(): Generator
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
