<?php

namespace Phpactor\Rename\Tests\Adapter\WorseReflection;

use Generator;
use Phpactor\Rename\Adapter\WorseReflection\WorseReflectionMemberRenamer;
use Phpactor\Rename\Model\Renamer;
use Phpactor\Rename\Tests\RenamerTestCase;
use Phpactor\WorseReflection\Reflector;

class WorseReflectionMemberRenamerTest extends RenamerTestCase
{
    /**
     * @return Generator<string,array<mixed>>
     */
    public function provideRename(): Generator
    {
        yield from $this->renames();
    }

    protected function createRenamer(): Renamer
    {
        return new WorseReflectionMemberRenamer(
            $this->reflector,
        );
    }

    /**
     * @return Generator<string,array<mixed>>
     */
    private function renames(): Generator
    {
        yield 'private method declaration' => [
            'member_renamer/method_declaration_private',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('Foo\ClassOne');
                $method = $reflection->methods()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $method->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('Foo\ClassOne');
                self::assertTrue($reflection->methods()->has('newName'));
            }
        ];
        yield 'private static method declaration' => [
            'member_renamer/method_declaration_static_private',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $method = $reflection->methods()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $method->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->methods()->has('newName'));
            }
        ];
        yield 'private property declaration' => [
            'member_renamer/property_declaration_private',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $method = $reflection->properties()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $method->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->properties()->has('newName'));
            }
        ];
        yield 'private static property declaration' => [
            'member_renamer/property_declaration_static_private',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $method = $reflection->properties()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $method->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->properties()->has('newName'));
            }
        ];
        yield 'private promoted property declaration' => [
            'member_renamer/property_promoted_private',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $method = $reflection->properties()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $method->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->properties()->has('newName'));
            }
        ];
    }
}
