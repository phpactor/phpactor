<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use Generator;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\WorseReflection\Core\Inference\GenericTypeResolver;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\ReflectorBuilder;

class GenericTypeResolverTest extends TestCase
{
    /**
     * @dataProvider provideMethodResolve
     * @param string[] $source
     * @param Type[] $arguments
     */
    public function testMethodResolve(array $source, string $className, string $memberName, array $arguments, string $expected): void
    {
        $source = '<?php ' . "\n" . implode("\n", $source);
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        $reflectionClass = $reflector->reflectClass($className);
        $member = $reflectionClass->members()->byName($memberName)->first();

        $classType = new GenericClassType($reflector, $reflectionClass->name(), $arguments);

        $resolver = new GenericTypeResolver();
        self::assertEquals($expected, $resolver->resolveMemberType($classType, $member)->__toString());
    }

    /**
     * @return Generator<array{array<int,string>,string,string,array<int,Type>,string}>
     */
    public function provideMethodResolve(): Generator
    {
        yield [
            [
                '/** @template T */',
                'class A {',
                '    /** @return T */',
                '    public function method() {}',
                '}',
            ],
            'A',
            'method',
            [
                TypeFactory::string()
            ],
            'string',
        ];

        yield [
            [
                '/** @template T */',
                'class B {}',
                '/** @template T */',
                'class A {',
                '    /** @return B<T> */',
                '    public function method() {}',
                '}',
            ],
            'A',
            'method',
            [
                TypeFactory::string()
            ],
            'B<string>',
        ];

        yield [
            [
                '/** @template T */',
                'class B {',
                '    /** @return T */',
                '    public function method() {}',
                '}',
                '/**',
                ' * @template T',
                ' * @extends B<int>',
                ' */',
                'class A extends B {',
                '}',
            ],
            'A',
            'method',
            [
            ],
            'int',
        ];

        yield [
            [
                '/** @template T */',
                'class B {',
                '    /** @return T */',
                '    public function method() {}',
                '}',
                '/**',
                ' * @template T',
                ' * @extends B<int>',
                ' */',
                'class A extends B {',
                '}',
            ],
            'A',
            'method',
            [
            ],
            'int',
        ];

        yield 'extends class' => [
            [
                '/**',
                ' * @template TKey',
                ' * @template TValue',
                ' */',
                'class B {',
                '    /** @return array<TKey,TValue> */',
                '    public function method() {}',
                '}',
                '/**',
                ' * @template T',
                ' * @extends B<int, T>',
                ' */',
                'class A extends B {',
                '}',
            ],
            'A',
            'method',
            [
                TypeFactory::string(),
            ],
            'array<int,string>',
        ];

        yield 'extends interface' => [
            [
                '/**',
                ' * @template TKey',
                ' * @template TValue',
                ' */',
                'interface B {',
                '    /** @return array<TKey,TValue> */',
                '    public function method();',
                '}',
                '/**',
                ' * @template T',
                ' * @implements B<int, T>',
                ' */',
                'class A implements B {',
                '    public function method() {}',
                '}',
            ],
            'A',
            'method',
            [
                TypeFactory::string(),
            ],
            'array<int,string>',
        ];

        yield [
            [
                '/** @template T */',
                'class B {}',
                '/** @template T */',
                'class A {',
                '    /** @return B<T> */',
                '    public function method() {}',
                '}',
                '/**',
                ' * @template TValue',
                ' * @extends A<TValue>',
                ' */',
                'class C extends A {}',
            ],
            'C',
            'method',
            [
                TypeFactory::string()
            ],
            'B<string>',
        ];
    }
}
