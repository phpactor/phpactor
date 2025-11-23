<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;

class ClassReflectorTest extends IntegrationTestCase
{
    #[DataProvider('provideReflectClassSuccess')]
    public function testReflectClassSuccess(string $source, string $class, string $method, string $expectedType): void
    {
        $reflection = $this->createReflector($source)->$method($class);
        $this->assertInstanceOf($expectedType, $reflection);
    }

    /**
     * @return Generator<string, array{string, string, string, class-string}>
     */
    public static function provideReflectClassSuccess(): Generator
    {
        yield 'Class' => [
             '<?php class Foobar {}',
             'Foobar',
             'reflectClass',
             ReflectionClass::class
         ];
        yield 'Interface' => [
            '<?php interface Foobar {}',
            'Foobar',
            'reflectInterface',
            ReflectionInterface::class
        ];
        yield 'Trait' => [
            '<?php trait Foobar {}',
            'Foobar',
            'reflectTrait',
            ReflectionTrait::class
        ];
    }

    #[DataProvider('provideReflectClassNotCorrectType')]
    public function testReflectClassNotCorrectType(string $source, string $class, string $method, string $expectedErrorMessage): void
    {
        $this->expectException(ClassNotFound::class);
        $this->expectExceptionMessage($expectedErrorMessage);

        $this->createReflector($source)->$method($class);
    }

    /**
     * @return Generator<string,array{string,string,string,string}>
     */
    public static function provideReflectClassNotCorrectType(): Generator
    {
        yield 'Class' => [
            '<?php trait Foobar {}',
            'Foobar',
            'reflectClass',
            '"Foobar" is not a class',
        ];
        yield 'Interface' => [
            '<?php class Foobar {}',
            'Foobar',
            'reflectInterface',
            '"Foobar" is not an interface',
        ];
        yield 'Trait' => [
            '<?php interface Foobar {}',
            'Foobar',
            'reflectTrait',
            '"Foobar" is not a trait',
        ];
    }
}
