<?php

declare(strict_types=1);

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser\Refactor;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\AliasAlreadyUsedException;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\ClassIsCurrentClassException;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameAlreadyImportedException;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameAlreadyInNamespaceException;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameImport;
use Phpactor\CodeTransform\Tests\Adapter\TolerantParser\TolerantTestCase;
use Phpactor\TextDocument\TextEdits;

abstract class AbstractTolerantImportNameCase extends TolerantTestCase
{
    #[DataProvider('provideImportClass')]
    public function testImportClass(string $test, string $name, ?string $alias = null): void
    {
        [$expected, $transformed] = $this->importNameFromTestFile('class', $test, $name, $alias);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    abstract public static function provideImportClass(): Generator;

    public function testThrowsExceptionIfClassAlreadyImported(): void
    {
        $this->expectException(NameAlreadyImportedException::class);
        $this->expectExceptionMessage('Class "DateTime" is already imported');
        $this->importNameFromTestFile('class', 'importClass1.test', 'DateTime');
    }

    public function testThrowsExceptionIfImportedClassIsTheCurrentClass1(): void
    {
        $this->expectException(ClassIsCurrentClassException::class);
        $this->expectExceptionMessage('Class "Foobar" is the current class');
        $this->importName('<?php class Foobar {}', 14, NameImport::forClass('Foobar'));
    }

    public function testThrowsExceptionClassIsCurrentClassExceptionTrait(): void
    {
        $this->expectException(ClassIsCurrentClassException::class);
        $this->expectExceptionMessage('Class "Foobar" is the current class');
        $this->importName('<?php trait Foobar {}', 14, NameImport::forClass('Foobar'));
    }

    public function testThrowsExceptionClassIsCurrentClassExceptionInterface(): void
    {
        $this->expectException(ClassIsCurrentClassException::class);
        $this->expectExceptionMessage('Class "Foobar" is the current class');
        $this->importName('<?php interface Foobar {}', 14, NameImport::forClass('Foobar'));
    }

    public function testThrowsExceptionIfAliasAlreadyUsed(): void
    {
        $this->expectException(AliasAlreadyUsedException::class);
        $this->expectExceptionMessage('Class alias "DateTime" is already used');
        $this->importNameFromTestFile('class', 'importClass1.test', 'Foobar', 'DateTime');
    }

    public function testThrowsNameAlreadyImportedExistingAliasName(): void
    {
        try {
            $this->importName(
                '<?php namespace Foo; use Foo1\Bar; use Foo2\Bar as Foo2Bar;',
                55,
                NameImport::forClass('Foo2\Bar')
            );
            self::fail('Expected NameAlreadyImportedException has not been raised');
        } catch (NameAlreadyImportedException $error) {
            self::assertSame('Class "Bar" is already imported', $error->getMessage());
            self::assertSame('Bar', $error->name());
            self::assertSame('Foo2Bar', $error->existingName());
            self::assertSame('Foo2\Bar', $error->existingFQN());
        }
    }

    public function testThrowsNameAlreadyImportedNameInUse(): void
    {
        try {
            $this->importName(
                '<?php namespace Foo; use Foo1\Bar;',
                34,
                NameImport::forClass('Foo2\Bar')
            );
            self::fail('Expected NameAlreadyImportedException has not been raised');
        } catch (NameAlreadyImportedException $error) {
            self::assertSame('Class "Bar" is already imported', $error->getMessage());
            self::assertSame('Bar', $error->name());
            self::assertSame('Bar', $error->existingName());
            self::assertSame('Foo1\Bar', $error->existingFQN());
        }
    }

    public function testThrowsNameAlreadyImportedOnlyAliasName(): void
    {
        try {
            $this->importName(
                '<?php namespace Foo; use Foo2\Bar as Foo2Bar;',
                45,
                NameImport::forClass('Foo2\Bar')
            );
            self::fail('Expected NameAlreadyImportedException has not been raised');
        } catch (NameAlreadyImportedException $error) {
            self::assertSame('Class "Bar" is already imported', $error->getMessage());
            self::assertSame('Bar', $error->name());
            self::assertSame('Foo2Bar', $error->existingName());
            self::assertSame('Foo2\Bar', $error->existingFQN());
        }
    }

    public function testThrowsNameAlreadyImportedFunction(): void
    {
        try {
            $this->importName(
                '<?php use function in_array;',
                55,
                NameImport::forFunction('in_array')
            );
            self::fail('Expected NameAlreadyImportedException has not been raised');
        } catch (NameAlreadyImportedException $error) {
            self::assertSame('Function "in_array" is already imported', $error->getMessage());
            self::assertSame('in_array', $error->name());
            self::assertSame('in_array', $error->existingName());
            self::assertSame('in_array', $error->existingFQN());
        }
    }

    public function testThrowsNameAlreadyImportedFunctionAlias(): void
    {
        try {
            $this->importName(
                '<?php use function in_array as foo_in_array;',
                55,
                NameImport::forFunction('in_array')
            );
            self::fail('Expected NameAlreadyImportedException has not been raised');
        } catch (NameAlreadyImportedException $error) {
            self::assertSame('Function "in_array" is already imported', $error->getMessage());
            self::assertSame('in_array', $error->name());
            self::assertSame('foo_in_array', $error->existingName());
            self::assertSame('in_array', $error->existingFQN());
        }
    }

    public function testThrowsExceptionIfImportedClassHasSameNameAsCurrentClassName(): void
    {
        try {
            $this->importName(
                '<?php namespace Barfoo; class Foobar extends Foobar',
                47,
                NameImport::forClass('BazBar\Foobar')
            );
            self::fail('Expected NameAlreadyImportedException has not been raised');
        } catch (NameAlreadyImportedException $error) {
            self::assertSame('Class "Foobar" is already imported', $error->getMessage());
            self::assertSame('Foobar', $error->name());
            self::assertSame('Foobar', $error->existingName());
            self::assertSame('Barfoo\Foobar', $error->existingFQN());
        }
    }

    public function testThrowsExceptionIfImportedClassHasSameNameAsCurrentInterfaceName(): void
    {
        $this->expectException(NameAlreadyImportedException::class);
        $this->importName(
            '<?php namespace Barfoo; interface Foobar extends Foobar',
            50,
            NameImport::forClass('BazBar\Foobar')
        );
    }

    public function testThrowsExceptionIfImportedClassInSameNamespace(): void
    {
        $this->expectException(NameAlreadyInNamespaceException::class);
        $this->expectExceptionMessage('Class "Barfoo" is in the same namespace as current class');
        $source = <<<'EOT'
            <?php

            namespace Barfoo;
            class Foobar {
                public function use(Barfoo $barfoo) {}
                }
            }
            EOT;
        $this->importName($source, 64, NameImport::forClass('Barfoo\Barfoo'));
    }

    #[DataProvider('provideImportFunction')]
    public function testImportFunction(string $test, string $name, ?string $alias = null): void
    {
        [$expected, $transformed] = $this->importNameFromTestFile('function', $test, $name, $alias);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    abstract public static function provideImportFunction(): Generator;

    abstract protected function importName(string $source, int $offset, NameImport $nameImport, bool $importGlobals = true): TextEdits;

    /**
     * @return array{string,string}
     */
    private function importNameFromTestFile(string $type, string $test, string $name, ?string $alias = null): array
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);
        $edits = TextEdits::none();

        if ($type === 'class') {
            $edits = $this->importName($source, $offset, NameImport::forClass($name, $alias));
        }

        if ($type === 'function') {
            $edits = $this->importName($source, $offset, NameImport::forFunction($name, $alias));
        }

        return [$expected, $edits->apply($source)];
    }
}
