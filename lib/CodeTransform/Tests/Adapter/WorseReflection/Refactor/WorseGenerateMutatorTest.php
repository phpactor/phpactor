<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

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
     * @return array<array-key, list<mixed>>
     */
    public function provideExtractMutator()
    {
        $propertyName = 'method';

        return [
            'property' => [
                'generateMutator1.test',
                $propertyName,
            ],
            'prefix and ucfirst' => [
                'generateMutator2.test',
                $propertyName,
                'set',
                true,
            ],
            'return type' => [
                'generateMutator3.test',
                $propertyName,
            ],
            'namespaced' => [
                'generateMutator4.test',
                $propertyName,
            ],
            'pseudo-type' => [
                'generateMutator5.test',
                $propertyName,
            ],
            'multiple-classes' => [
                'generateMutator6.test',
                $propertyName,
            ],
            'prefix but ucfirst by default' => [
                'generateMutator7.test',
                $propertyName,
                'set',
            ],
            'prefix but ucfirst to false' => [
                'generateMutator8.test',
                $propertyName,
                'set',
                false,
            ],
            'fluent' => [
                'generateMutator9.test',
                $propertyName,
                '',
                false,
                true,
            ],
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
