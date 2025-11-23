<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateAccessor;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;

class WorseGenerateAccessorTest extends WorseTestCase
{
    #[DataProvider('provideExtractAccessor')]
    public function testGenerateAccessor(
        string $test,
        string $propertyName,
        string $prefix = '',
        ?bool $upperCaseFirst = null
    ): void {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(
            __DIR__ . '/fixtures/' . $test
        );

        $generateAccessor = new WorseGenerateAccessor(
            $this->reflectorForWorkspace($source),
            $this->updater(),
            $prefix,
            $upperCaseFirst
        );
        $transformed = $generateAccessor->generate(
            SourceCode::fromString($source),
            [$propertyName],
            $offset
        )->apply($source);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    public static function provideExtractAccessor(): Generator
    {
        $propertyName = 'method';

        yield'property' => [
            'generateAccessor1.test',
            $propertyName,
        ];
        yield'prefix and ucfirst' => [
            'generateAccessor2.test',
            $propertyName,
            'get',
            true,
        ];
        yield 'return type' => [
            'generateAccessor3.test',
            $propertyName,
        ];
        yield 'namespaced' => [
            'generateAccessor4.test',
            $propertyName,
        ];
        yield 'pseudo-type' => [
            'generateAccessor5.test',
            $propertyName,
        ];
        yield 'multiple-classes' => [
            'generateAccessor6.test',
            $propertyName,
        ];
        yield 'prefix but ucfirst by default' => [
            'generateAccessor7.test',
            $propertyName,
            'get',
        ];
        yield 'prefix but ucfirst to false' => [
            'generateAccessor8.test',
            $propertyName,
            'get',
            false,
        ];
    }

    public function testNonProperty(): void
    {
        $this->expectException(ItemNotFound::class);
        $this->expectExceptionMessage('Unknown item "bar", known items: "foo"');
        $source = '<?php class Foo { private $foo; }';

        $generateAccessor = new WorseGenerateAccessor($this->reflectorForWorkspace(''), $this->updater());
        $generateAccessor->generate(SourceCode::fromString($source), ['bar'], 0);
    }
}
