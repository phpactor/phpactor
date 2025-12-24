<?php

namespace Phpactor\Rename\Tests\Integration\Adapter\ReferenceFinder;

use Closure;
use Generator;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\TextDocument\FilesystemTextDocumentLocator;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedImplementationFinder;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedReferenceFinder;
use Phpactor\Rename\Adapter\ReferenceFinder\MemberRenamer;
use Phpactor\Rename\Model\Renamer;
use Phpactor\Rename\Tests\RenamerTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionPropertyAccess;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Reflector;

class MemberRenamerTest extends RenamerTestCase
{
    /**
     * @return Generator<string,array{string,Closure(Reflector,Renamer): Generator,Closure(Reflector): void}>
     */
    public function provideRename(): Generator
    {
        yield from $this->methodRenames();
        yield from $this->propertyRenames();
        yield from $this->constantRenames();
        yield from $this->traitRenames();
        yield from $this->enumRenames();
    }

    protected function createRenamer(): Renamer
    {
        $finder = new IndexedReferenceFinder(
            $this->indexAgent->query(),
            $this->reflector
        );
        return new MemberRenamer(
            $finder,
            new FilesystemTextDocumentLocator(),
            new \Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider(),
            new IndexedImplementationFinder($this->indexAgent->query(), $this->reflector)
        );
    }

    /**
     * @return Generator<string,array{string,Closure(Reflector,Renamer): Generator,Closure(Reflector): void}>
     */
    private function methodRenames(): Generator
    {
        yield 'method declaration' => [
            'member_renamer/method_declaration',
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

        yield 'attributed method declaration' => [
            'member_renamer/method_declaration',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $method = $reflection->methods()->get('złom');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $method->nameRange()->start(),
                    'scrap'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->methods()->has('scrap'));
            }
        ];

        yield 'method reference' => [
            'member_renamer/method_declaration',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $methodCalls = $reflector->navigate(TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build())->methodCalls();
                $first = $methodCalls->first();
                assert($first instanceof ReflectionMethodCall);

                return $renamer->rename(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build(),
                    $first->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $methodCalls = $reflector->navigate(TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build())->methodCalls();
                $first = $methodCalls->first();
                self::assertEquals('newName', $first->name());
            }
        ];
    }

    /**
     * @return Generator<string,array{string,Closure(Reflector,Renamer): Generator,Closure(Reflector): void}>
     */
    private function propertyRenames(): Generator
    {
        yield 'property declaration private' => [
            'member_renamer/property_declaration_private',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $property = $reflection->properties()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $property->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->properties()->has('newName'));
            }
        ];

        yield 'property declaration protected' => [
            'member_renamer/property_declaration_protected',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $property = $reflection->properties()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $property->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $propertyAccesses = $reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build()
                )->propertyAccesses();
                $first = $propertyAccesses->first();
                self::assertEquals('newName', $first->name());
            }
        ];

        yield 'property declaration public' => [
            'member_renamer/property_declaration_public',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $property = $reflection->properties()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $property->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $propertyAccesses = $reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build()
                )->propertyAccesses();
                $first = $propertyAccesses->first();
                self::assertEquals('newName', $first->name());
                $propertyAccesses = $reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/test.php'))->build()
                )->propertyAccesses();
                $first = $propertyAccesses->first();
                self::assertEquals('newName', $first->name());
            }
        ];

        yield 'attributed property declaration public' => [
            'member_renamer/property_declaration_public',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $property = $reflection->properties()->get('found');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $property->nameRange()->start(),
                    'results'
                );
            },
            function (Reflector $reflector): void {
                $propertyAccesses = [...$reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build()
                )->propertyAccesses()];
                $property = end($propertyAccesses);
                self::assertInstanceOf(ReflectionPropertyAccess::class, $property);
                self::assertEquals('results', $property->name());

                $propertyAccesses = [...$reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/test.php'))->build()
                )->propertyAccesses()->getIterator()];
                $property = end($propertyAccesses);
                self::assertInstanceOf(ReflectionPropertyAccess::class, $property);
                self::assertEquals('results', $property->name());
            }
        ];

        yield 'property declaration generic' => [
            'member_renamer/property_declaration_public_generic',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $property = $reflection->properties()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $property->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $propertyAccesses = $reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build()
                )->propertyAccesses();
                $first = $propertyAccesses->first();
                self::assertEquals('newName', $first->name());
                $propertyAccesses = $reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/test.php'))->build()
                )->propertyAccesses();
                $first = $propertyAccesses->first();
                self::assertEquals('newName', $first->name());
            }
        ];

        yield 'property promoted declaration public' => [
            'member_renamer/property_promoted_declaration_public',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('Test\ClassOne');
                $property = $reflection->properties()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $property->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $propertyAccesses = $reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build()
                )->propertyAccesses();
                $first = $propertyAccesses->first();
                self::assertEquals('newName', $first->name());
                $propertyAccesses = $reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/test.php'))->build()
                )->propertyAccesses();
                $first = $propertyAccesses->first();
                self::assertEquals('newName', $first->name());
            }
        ];

        yield 'attributed promoted property declaration public' => [
            'member_renamer/property_promoted_declaration_public',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('Test\ClassOne');
                $property = $reflection->properties()->get('depOld');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $property->nameRange()->start(),
                    'depNew'
                );
            },
            function (Reflector $reflector): void {
                $propertyAccesses = [...$reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build()
                )->propertyAccesses()];
                $property = end($propertyAccesses);
                self::assertInstanceOf(ReflectionPropertyAccess::class, $property);
                self::assertEquals('depNew', $property->name());
                $propertyAccesses = [...$reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/test.php'))->build()
                )->propertyAccesses()];
                $property = end($propertyAccesses);
                self::assertInstanceOf(ReflectionPropertyAccess::class, $property);
                self::assertEquals('depNew', $property->name());
            }
        ];

        yield 'property declaration public does not rename other members' => [
            'member_renamer/property_declaration_public_does_not_rename_others',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $property = $reflection->properties()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $property->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->properties()->has('barfoo'));
                self::assertTrue($reflection->properties()->has('bazbar'));
                self::assertTrue($reflection->properties()->has('newName'));
            }
        ];
    }

    /**
     * @return Generator<string,array{string,Closure(Reflector,Renamer): Generator,Closure(Reflector): void}>
     */
    private function constantRenames(): Generator
    {
        yield 'constant declaration private' => [
            'member_renamer/constant_declaration_private',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $constant = $reflection->constants()->get('BAR');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $constant->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->constants()->has('newName'));
            }
        ];
        yield 'constant declaration protected' => [
            'member_renamer/constant_declaration_protected',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $constant = $reflection->constants()->get('BAR');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $constant->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->constants()->has('newName'));

                $propertyAccesses = $reflector->navigate(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build()
                )->constantAccesses();
                $first = $propertyAccesses->first();
                self::assertEquals('newName', $first->name());
            }
        ];
        yield 'constant declaration public' => [
            'member_renamer/constant_declaration_public',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $constant = $reflection->constants()->get('FOO');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $constant->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->constants()->has('newName'));
            }
        ];
        yield 'attributed constant declaration public' => [
            'member_renamer/constant_declaration_public',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $constant = $reflection->constants()->get('ZOO');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $constant->nameRange()->start(),
                    'ZŁOM'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->constants()->has('ZŁOM'));
            }
        ];
    }

    /**
     * @return Generator<string,array{string,Closure(Reflector,Renamer): Generator,Closure(Reflector): void}>
     */
    private function enumRenames(): Generator
    {
        yield 'enum case declaration private' => [
            'member_renamer/enum_case_declaration_private',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectEnum('ClassOne');
                $enum = $reflection->cases()->get('BAR');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $enum->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectEnum('ClassOne');
                self::assertTrue($reflection->cases()->has('newName'));
            }
        ];

        yield 'enum attributed case declaration' => [
            'member_renamer/enum_case_declaration_private',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectEnum('ClassOne');
                $enum = $reflection->cases()->get('BAZ');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $enum->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectEnum('ClassOne');
                self::assertTrue($reflection->cases()->has('newName'));
            }
        ];
    }

    /**
     * @return Generator<string,array{string,Closure(Reflector,Renamer): Generator,Closure(Reflector): void}>
     */
    private function traitRenames(): Generator
    {
        yield 'trait use with insteadof' => [
            'member_renamer/trait_insteadof',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectTrait('A');
                $method = $reflection->methods()->get('smallTalk');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $method->nameRange()->start(),
                    'foobar'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectTrait('A');
                self::assertTrue($reflection->methods()->has('foobar'));
            }
        ];
        yield 'trait use with insteadof rename alias' => [
            'member_renamer/trait_insteadof',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectTrait('A');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    ByteOffset::fromInt(311),
                    'foobar'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectTrait('A');
                self::assertTrue($reflection->methods()->has('foobar'));
            }
        ];
    }
}
