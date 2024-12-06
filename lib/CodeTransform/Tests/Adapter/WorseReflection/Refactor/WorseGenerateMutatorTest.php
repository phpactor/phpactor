<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Generator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateMutator;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;

class WorseGenerateMutatorTest extends WorseTestCase
{
    /**
     * @dataProvider provideExtractMutator
     */
    public function testGenerateMutator(
        string $test,
        string $propertyName,
        string $prefix = '',
        ?bool $upperCaseFirst = null,
        bool $fluent = false
    ): void {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(
            __DIR__ . '/fixtures/' . $test
        );

        $generateMutator = new WorseGenerateMutator(
            $this->reflectorForWorkspace($source),
            $this->updater(),
            $prefix,
            $upperCaseFirst,
            $fluent,
        );
        $transformed = $generateMutator->generate(
            SourceCode::fromString($source),
            [$propertyName],
            $offset
        )->apply($source);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return Generator<list<mixed>>
     */
    public function provideExtractMutator(): Generator
    {
        $propertyName = 'method';

        yield 'property' => [
            'generateMutator1.test',
            $propertyName,
        ];
        yield 'prefix and ucfirst' => [
            'generateMutator2.test',
            $propertyName,
            'set',
            true,
        ];
        yield 'return type' => [
            'generateMutator3.test',
            $propertyName,
        ];
        yield 'namespaced' => [
            'generateMutator4.test',
            $propertyName,
        ];
        yield 'pseudo-type' => [
            'generateMutator5.test',
            $propertyName,
        ];
        yield 'multiple-classes' => [
            'generateMutator6.test',
            $propertyName,
        ];
        yield 'prefix but ucfirst by default' => [
            'generateMutator7.test',
            $propertyName,
            'set',
        ];
        yield 'prefix but ucfirst to false' => [
            'generateMutator8.test',
            $propertyName,
            'set',
            false,
        ];
        yield 'fluent' => [
            'generateMutator9.test',
            $propertyName,
            '',
            false,
            true,
        ];
        yield 'synthetic types' => [
            'generateMutator10.test',
            $propertyName,
        ];
    }

    public function testNonProperty(): void
    {
        $this->expectException(ItemNotFound::class);
        $this->expectExceptionMessage('Unknown item "bar", known items: "foo"');
        $source = '<?php class Foo { private $foo; }';

        $generateMutator = new WorseGenerateMutator($this->reflectorForWorkspace(''), $this->updater());
        $generateMutator->generate(SourceCode::fromString($source), ['bar'], 0);
    }
}
