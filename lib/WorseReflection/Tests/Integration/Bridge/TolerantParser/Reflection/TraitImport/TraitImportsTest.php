<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection\TraitImport;

use PHPUnit\Framework\Attributes\DataProvider;
use Closure;
use Generator;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitAlias;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitImports;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class TraitImportsTest extends IntegrationTestCase
{
    #[DataProvider('provideTraitImports')]
    public function testTraitImports(string $source, Closure $assertion): void
    {
        $rootNode = $this->parseSource($source);
        $classDeclaration = $rootNode->getFirstDescendantNode(ClassDeclaration::class);
        $assertion(TraitImports::forClassDeclaration($classDeclaration));
    }

    /**
     * @return Generator<string, array{string, Closure(TraitImports):void}>
     */
    public function provideTraitImports(): Generator
    {
        yield 'simple use' => [
            '<?php trait A {}; class B { use A; }',
            function (TraitImports $traitImports): void {
                $this->assertCount(1, $traitImports);
                $this->assertTrue($traitImports->has('A'));
                $this->assertEquals('A', $traitImports->get('A')->name());
            }
        ];

        yield 'incomplete statement' => [
            '<?php trait A {}; class B { use }',
            function (TraitImports $traitImports): void {
                $this->assertCount(0, $traitImports);
            }
        ];

        yield 'simple use with alias' => [
            '<?php trait A { function foo() {}}; class B { use A { foo as bar; }',
            function (TraitImports $traitImports): void {
                $this->assertCount(1, $traitImports);
                $traitImport = $traitImports->get('A');
                $this->assertCount(1, $traitImport->traitAliases());
                $traitAlias = $traitImport->traitAliases()['foo'];
                assert($traitAlias instanceof TraitAlias);
                $this->assertEquals('foo', $traitAlias->originalName());
                $this->assertEquals('bar', $traitAlias->newName());
            }
        ];

        yield 'simple use with alias and visiblity' => [
            '<?php trait A { function foo() {}}; class B { use A { foo as private bar; bar as protected bar; zed as public aar;}',
            function (TraitImports $traitImports): void {
                $traitImport = $traitImports->get('A');
                ;
                $this->assertEquals(Visibility::private(), $traitImport->traitAliases()['foo']->visiblity());
                $this->assertEquals(Visibility::protected(), $traitImport->traitAliases()['bar']->visiblity());
                $this->assertEquals(Visibility::public(), $traitImport->traitAliases()['zed']->visiblity());
            }
        ];

        yield 'does not support insteadof' => [
            '<?php trait A { function foo(){} function bar()}}' .
            'trait B { function foo() {}} ' .
            'class B { use A, B { B::foo insteadof A } }',
            function (TraitImports $traitImports): void {
                $traitImport = $traitImports->get('A');
                $this->assertCount(0, $traitImport->traitAliases());
            }
        ];

        yield 'multiple traits with single alias maping' => [
            '<?php trait A { function foo(){} }' .
            'trait B { function bar(){} } ' .
            'class B { use A, B { foo as foo1; bar as bar1 } }',
            function (TraitImports $traitImports): void {
                $this->assertCount(2, $traitImports);
                $traitImport = $traitImports->get('A');
                $this->assertCount(2, $traitImport->traitAliases());

                $traitImport = $traitImports->get('B');
                $this->assertCount(2, $traitImport->traitAliases());
            }
        ];
    }
}
