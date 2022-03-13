<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateAccessor;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;

class WorseGenerateAccessorTest extends WorseTestCase
{
    /**
     * @dataProvider provideExtractAccessor
     */
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

    public function provideExtractAccessor()
    {
        $propertyName = 'method';

        return [
            'property' => [
                'generateAccessor1.test',
                $propertyName,
            ],
            'prefix and ucfirst' => [
                'generateAccessor2.test',
                $propertyName,
                'get',
                true,
            ],
            'return type' => [
                'generateAccessor3.test',
                $propertyName,
            ],
            'namespaced' => [
                'generateAccessor4.test',
                $propertyName,
            ],
            'pseudo-type' => [
                'generateAccessor5.test',
                $propertyName,
            ],
            'multiple-classes' => [
                'generateAccessor6.test',
                $propertyName,
            ],
            'prefix but ucfirst by default' => [
                'generateAccessor7.test',
                $propertyName,
                'get',
            ],
            'prefix but ucfirst to false' => [
                'generateAccessor8.test',
                $propertyName,
                'get',
                false,
            ],
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
