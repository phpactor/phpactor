<?php

namespace Phpactor\CodeBuilder\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\WorseReflection\Core\TypeFactory;

abstract class UpdaterTestCase extends TestCase
{
    /**
     * @dataProvider provideClassImport
     * @dataProvider provideFunctionImport
     */
    public function testNamespaceAndUse(string $existingCode, SourceCode $prototype, string $expectedCode): void
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideClassImport()
    {
        yield 'It does nothing when given an empty source code prototype' => [

                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->build(),
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
            ];

        yield 'It does not change the namespace if it is the same' => [

                <<<'EOT'
                    namespace Animal\Kingdom;

                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->namespace('Animal\Kingdom')->build(),
                <<<'EOT'
                    namespace Animal\Kingdom;

                    class Aardvark
                    {
                    }
                    EOT
            ];

        yield 'It adds the namespace if it doesnt exist' => [

                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->namespace('Animal\Kingdom')->build(),
                <<<'EOT'
                    namespace Animal\Kingdom;

                    class Aardvark
                    {
                    }
                    EOT
            ];

        yield 'It updates the namespace' => [

                <<<'EOT'
                    namespace Animal\Kingdom;

                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->namespace('Bovine\Kingdom')->build(),
                <<<'EOT'
                    namespace Bovine\Kingdom;

                    class Aardvark
                    {
                    }
                    EOT
            ];

        yield 'It adds use statements' => [

                <<<'EOT'
                    $bovine = new Bovine();
                    EOT
                , SourceCodeBuilder::create()->use('Foo\Bovine')->build(),
                <<<'EOT'

                    use Foo\Bovine;

                    $bovine = new Bovine();
                    EOT
            ];

        yield 'It adds use statements with an alias' => [

                <<<'EOT'
                    // test
                    $bovine = new Bovine();
                    EOT
                , SourceCodeBuilder::create()->use('Foo\Bovine', 'Cow')->build(),
                <<<'EOT'

                    use Foo\Bovine as Cow;

                    // test
                    $bovine = new Bovine();
                    EOT
            ];

        yield 'It adds use statements with an alias with existing imports' => [

                <<<'EOT'
                    use Foo\Dino;

                    // test
                    $bovine = new Bovine();
                    EOT
                , SourceCodeBuilder::create()->use('Foo\Bovine', 'Cow')->build(),
                <<<'EOT'
                    use Foo\Bovine as Cow;
                    use Foo\Dino;

                    // test
                    $bovine = new Bovine();
                    EOT
            ];

        yield 'It adds use statements after a namespace' => [

                <<<'EOT'
                    namespace Kingdom;

                    $bovine = new Bovine();
                    EOT
                , SourceCodeBuilder::create()->use('Bovine')->build(),
                <<<'EOT'
                    namespace Kingdom;

                    use Bovine;

                    $bovine = new Bovine();
                    EOT
            ];

        yield 'class import: It inserts use statements before the first lexicographically greater use statement' => [

                <<<'EOT'
                    namespace Kingdom;

                    use Aardvark;
                    use Badger;
                    use Antilope;
                    use Zebra;
                    use Primate;
                    EOT
                , SourceCodeBuilder::create()->use('Bovine')->build(),
                <<<'EOT'
                    namespace Kingdom;

                    use Aardvark;
                    use Badger;
                    use Antilope;
                    use Bovine;
                    use Zebra;
                    use Primate;
                    EOT
            ];

        yield 'class import: It inserts use statements just before the first lexicographically greater use statement' => [

                <<<'EOT'
                    namespace Kingdom;

                    use Zebra;
                    use Primate;
                    EOT
                , SourceCodeBuilder::create()->use('Bovine')->build(),
                <<<'EOT'
                    namespace Kingdom;

                    use Bovine;
                    use Zebra;
                    use Primate;
                    EOT
            ];

        yield 'class import: It inserts use statements after all lexicographically smaller use statements' => [

                <<<'EOT'
                    namespace Kingdom;

                    use Badger;
                    use Aardvark;
                    EOT
                , SourceCodeBuilder::create()->use('Bovine')->build(),
                <<<'EOT'
                    namespace Kingdom;

                    use Badger;
                    use Aardvark;
                    use Bovine;
                    EOT
            ];

        yield 'class import: It ignores existing use statements' => [

                <<<'EOT'
                    namespace Kingdom;

                    use Primate;
                    EOT
                , SourceCodeBuilder::create()->use('Primate')->build(),
                <<<'EOT'
                    namespace Kingdom;

                    use Primate;
                    EOT
            ];

        yield 'class import: It ignores repeated namespaced use statements' => [

                <<<'EOT'
                    namespace Kingdom;

                    EOT
                , SourceCodeBuilder::create()->use('Primate\Ape')->use('Primate\Ape')->build(),
                <<<'EOT'
                    namespace Kingdom;

                    use Primate\Ape;

                    EOT
            ];

        yield 'class import: It ignores existing aliased use statements' => [

                <<<'EOT'
                    namespace Kingdom;

                    use Primate as Foobar;
                    EOT
                , SourceCodeBuilder::create()->use('Primate')->build(),
                <<<'EOT'
                    namespace Kingdom;

                    use Primate as Foobar;
                    EOT
            ];

        yield 'class import: It appends multiple use statements' => [

                <<<'EOT'
                    namespace Kingdom;

                    use Primate;
                    EOT
                , SourceCodeBuilder::create()->use('Animal\Bovine')->use('Feline')->use('Canine')->build(),
                <<<'EOT'
                    namespace Kingdom;

                    use Animal\Bovine;
                    use Canine;
                    use Feline;
                    use Primate;
                    EOT
            ];

        yield 'class import: It maintains an empty line between the class and the use statements' => [

                <<<'EOT'
                    namespace Kingdom;

                    class Foobar
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->use('Feline')->build(),
                <<<'EOT'
                    namespace Kingdom;

                    use Feline;

                    class Foobar
                    {
                    }
                    EOT
            ];

        yield 'class import: It maintains an empty line between the trait and the use statements' => [

                <<<'EOT'
                    namespace Kingdom;

                    trait Foobar
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->use('Feline')->build(),
                <<<'EOT'
                    namespace Kingdom;

                    use Feline;

                    trait Foobar
                    {
                    }
                    EOT
            ];

        yield 'class import: it maintains empty line between class with no namespace' => [

                <<<'EOT'
                    class Foobar
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->use('Foo\Feline')->build(),
                <<<'EOT'

                    use Foo\Feline;

                    class Foobar
                    {
                    }
                    EOT
            ];

        yield 'class import: it maintains empty line between trait with no namespace' => [

                <<<'EOT'
                    trait Foobar
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->use('Foo\Feline')->build(),
                <<<'EOT'

                    use Foo\Feline;

                    trait Foobar
                    {
                    }
                    EOT
            ];

        yield 'class import: previously included class with a lexigraphically greater member before it' => [
                <<<'EOT'
                    <?php

                    namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

                    use Phpactor\WorseReflection\Core\Logger;
                    use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
                    use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
                    use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
                    EOT
                , SourceCodeBuilder::create()
                    ->use('Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike')
                ->build(),
                <<<'EOT'
                    <?php

                    namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

                    use Phpactor\WorseReflection\Core\Logger;
                    use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
                    use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
                    use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
                    EOT
            ];

        yield 'class import: It ignores classes in the same namespace' => [

                <<<'EOT'
                    namespace Animal;
                    EOT
                , SourceCodeBuilder::create()->use('Animal\Primate')->build()
                , <<<'EOT'
                    namespace Animal;
                    EOT
            ];

        yield 'it does not add additional space' => [

                <<<'EOT'
                    namespace Animal;

                    class Foo {}
                    EOT
                , SourceCodeBuilder::create()->use('Animal\Primate')->build()
                , <<<'EOT'
                    namespace Animal;

                    class Foo {}
                    EOT
            ];
    }

    public function provideFunctionImport()
    {
        yield 'It adds use function statements' => [

                <<<'EOT'
                    hello('you');
                    EOT
                , SourceCodeBuilder::create()->useFunction('Foo\hello')->build(),
                <<<'EOT'

                    use function Foo\hello;

                    hello('you');
                    EOT
            ];

        yield 'It adds use function statements with an alias' => [

                <<<'EOT'

                    hello('you');
                    EOT
                , SourceCodeBuilder::create()->useFunction('Foo\hello', 'bar')->build(),
                <<<'EOT'

                    use function Foo\hello as bar;

                    hello('you');
                    EOT
            ];

        yield 'It adds use function statements after with an alias' => [

                <<<'EOT'
                    use function Foo\hello as boo;

                    hello('you');
                    EOT
                , SourceCodeBuilder::create()->useFunction('Foo\hello', 'bar')->build(),
                <<<'EOT'
                    use function Foo\hello as boo;
                    use function Foo\hello as bar;

                    hello('you');
                    EOT
            ];

        yield 'It ignores existing function imports' => [

                <<<'EOT'
                    use function Foo\hello as boo;

                    hello('you');
                    EOT
                , SourceCodeBuilder::create()->useFunction('Foo\hello', 'boo')->build(),
                <<<'EOT'
                    use function Foo\hello as boo;

                    hello('you');
                    EOT
            ];

        yield 'adds function imports after class imports' => [

                <<<'EOT'
                    use Foobar\Acme;
                    use Foobar\Hello;
                    use Foobar\Zoo;

                    hello('you');
                    EOT
                , SourceCodeBuilder::create()->useFunction('Foobar\Bello')->build(),
                <<<'EOT'
                    use Foobar\Acme;
                    use Foobar\Hello;
                    use Foobar\Zoo;
                    use function Foobar\Bello;

                    hello('you');
                    EOT
            ];
    }

    /**
     * @dataProvider provideClasses
     * @dataProvider provideMethodParameters
     */
    public function testClasses(string $existingCode, SourceCode $prototype, string $expectedCode): void
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideClasses()
    {
        yield 'It does nothing when prototype has only the class' => [

                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->class('Aardvark')->end()->build(),
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
            ];

        yield 'It adds a class to an empty file' => [

                <<<'EOT'
                    EOT
                , SourceCodeBuilder::create()->class('Anteater')->end()->build(),
                <<<'EOT'

                    class Anteater
                    {
                    }
                    EOT
            ];

        yield 'It adds a class' => [

                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->class('Anteater')->end()->build(),
                <<<'EOT'
                    class Aardvark
                    {
                    }

                    class Anteater
                    {
                    }
                    EOT
            ];

        yield 'It adds a class after a namespace' => [

                <<<'EOT'
                    namespace Animals;

                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->class('Anteater')->end()->build(),
                <<<'EOT'
                    namespace Animals;

                    class Aardvark
                    {
                    }

                    class Anteater
                    {
                    }
                    EOT
            ];

        yield 'It does not modify a class with a namespace' => [

                <<<'EOT'
                    namespace Animals;

                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->namespace('Animals')->class('Aardvark')->end()->build(),
                <<<'EOT'
                    namespace Animals;

                    class Aardvark
                    {
                    }
                    EOT
            ];

        yield 'It adds multiple classes' => [
                <<<'EOT'
                    EOT
                , SourceCodeBuilder::create()->class('Aardvark')->end()->class('Anteater')->end()->build(),
                <<<'EOT'

                    class Aardvark
                    {
                    }

                    class Anteater
                    {
                    }
                    EOT
            ];

        yield 'It extends a class' => [
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->class('Aardvark')->extends('Animal')->end()->build(),
                <<<'EOT'
                    class Aardvark extends Animal
                    {
                    }
                    EOT
            ];

        yield 'It modifies an existing extends' => [
                <<<'EOT'
                    class Aardvark extends Giraffe
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->class('Aardvark')->extends('Animal')->end()->build(),
                <<<'EOT'
                    class Aardvark extends Animal
                    {
                    }
                    EOT
            ];

        yield 'It is idempotent extends' => [
                <<<'EOT'
                    class Aardvark extends Animal
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->class('Aardvark')->extends('Animal')->end()->build(),
                <<<'EOT'
                    class Aardvark extends Animal
                    {
                    }
                    EOT
            ];

        yield 'It is implements an interface' => [
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->class('Aardvark')->implements('Animal')->end()->build(),
                <<<'EOT'
                    class Aardvark implements Animal
                    {
                    }
                    EOT
            ];

        yield 'It is implements implementss' => [
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->class('Aardvark')->implements('Zoo')->implements('Animal')->end()->build(),
                <<<'EOT'
                    class Aardvark implements Zoo, Animal
                    {
                    }
                    EOT
            ];

        yield 'It is adds implements' => [
                <<<'EOT'
                    class Aardvark implements Zoo
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->class('Aardvark')->implements('Animal')->end()->build(),
                <<<'EOT'
                    class Aardvark implements Zoo, Animal
                    {
                    }
                    EOT
            ];

        yield 'It ignores existing implements names' => [
                <<<'EOT'
                    class Aardvark implements Animal
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->class('Aardvark')->implements('Zoo')->implements('Animal')->end()->build(),
                <<<'EOT'
                    class Aardvark implements Animal, Zoo
                    {
                    }
                    EOT
            ];
    }

    /**
     * @dataProvider provideTraits
     */
    public function testTraits(string $existingCode, SourceCode $prototype, string $expectedCode): void
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideTraits()
    {
        yield 'It does nothing when prototype has only the trait' => [

                <<<'EOT'
                    trait Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->trait('Aardvark')->end()->build(),
                <<<'EOT'
                    trait Aardvark
                    {
                    }
                    EOT
            ];

        yield 'It adds a trait to an empty file' => [

                <<<'EOT'
                    EOT
                , SourceCodeBuilder::create()->trait('Anteater')->end()->build(),
                <<<'EOT'

                    trait Anteater
                    {
                    }
                    EOT
            ];

        yield 'It adds a trait' => [

                <<<'EOT'
                    trait Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->trait('Anteater')->end()->build(),
                <<<'EOT'
                    trait Aardvark
                    {
                    }

                    trait Anteater
                    {
                    }
                    EOT
            ];

        yield 'It adds a trait after a namespace' => [

                <<<'EOT'
                    namespace Animals;

                    trait Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->trait('Anteater')->end()->build(),
                <<<'EOT'
                    namespace Animals;

                    trait Aardvark
                    {
                    }

                    trait Anteater
                    {
                    }
                    EOT
            ];

        yield 'It does not modify a trait with a namespace' => [

                <<<'EOT'
                    namespace Animals;

                    trait Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->namespace('Animals')->trait('Aardvark')->end()->build(),
                <<<'EOT'
                    namespace Animals;

                    trait Aardvark
                    {
                    }
                    EOT
            ];

        yield 'It adds multiple traites' => [
                <<<'EOT'
                    EOT
                , SourceCodeBuilder::create()->trait('Aardvark')->end()->trait('Anteater')->end()->build(),
                <<<'EOT'

                    trait Aardvark
                    {
                    }

                    trait Anteater
                    {
                    }
                    EOT
            ];
    }

    /**
     * @dataProvider provideProperties
     */
    public function testProperties(string $existingCode, SourceCode $prototype, string $expectedCode): void
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideProperties()
    {
        yield 'It adds a property' => [
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public $propertyOne;
                    }
                    EOT
            ];

        yield 'It adds a property idempotently' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public $propertyOne;
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public $propertyOne;
                    }
                    EOT
            ];

        yield 'It adds a property with existing assigned property' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public $propertyOne = false;
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public $propertyOne = false;
                    }
                    EOT
            ];

        yield 'It adds a property after existing properties' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public $eyes
                        public $nose;
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public $eyes
                        public $nose;
                        public $propertyOne;
                    }
                    EOT
            ];

        yield 'It adds multiple properties' => [
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->end()->property('propertyTwo')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public $propertyOne;
                        public $propertyTwo;
                    }
                    EOT
            ];

        yield 'It adds a typed property' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public $eyes
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->type('Hello')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public $eyes

                        /**
                         * @var Hello
                         */
                        public $propertyOne;
                    }
                    EOT
        ];

        yield 'It adds a generic typed property' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public $eyes
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->type('Hello<Bar>')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public $eyes

                        /**
                         * @var Hello<Bar>
                         */
                        public $propertyOne;
                    }
                    EOT
            ];

        yield 'It adds a nullable typed property' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public $eyes
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->type(
                            TypeFactory::fromString('?Hello')
                        )->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public $eyes

                        /**
                         * @var ?Hello
                         */
                        public $propertyOne;
                    }
                    EOT
            ];

        yield 'It adds before methods' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function crawl()
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->type('Hello')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        /**
                         * @var Hello
                         */
                        public $propertyOne;

                        public function crawl()
                        {
                        }
                    }
                    EOT
            ];
    }

    /**
     * @dataProvider provideTraitProperties
     */
    public function testTraitProperties(string $existingCode, SourceCode $prototype, string $expectedCode): void
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideTraitProperties()
    {
        yield 'trait: It adds a property' => [
                <<<'EOT'
                    trait Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->trait('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    trait Aardvark
                    {
                        public $propertyOne;
                    }
                    EOT
            ];

        yield 'trait: It adds a property idempotently' => [
                <<<'EOT'
                    trait Aardvark
                    {
                        public $propertyOne;
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->trait('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    trait Aardvark
                    {
                        public $propertyOne;
                    }
                    EOT
            ];

        yield 'trait: It adds a property with existing assigned property' => [
                <<<'EOT'
                    trait Aardvark
                    {
                        public $propertyOne = false;
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->trait('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    trait Aardvark
                    {
                        public $propertyOne = false;
                    }
                    EOT
            ];

        yield 'trait: It adds a property after existing properties' => [
                <<<'EOT'
                    trait Aardvark
                    {
                        public $eyes
                        public $nose;
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->trait('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    trait Aardvark
                    {
                        public $eyes
                        public $nose;
                        public $propertyOne;
                    }
                    EOT
            ];

        yield 'trait: It adds multiple properties' => [
                <<<'EOT'
                    trait Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->trait('Aardvark')
                        ->property('propertyOne')->end()->property('propertyTwo')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    trait Aardvark
                    {
                        public $propertyOne;
                        public $propertyTwo;
                    }
                    EOT
            ];

        yield 'trait: It adds documented properties' => [
                <<<'EOT'
                    trait Aardvark
                    {
                        public $eyes
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->trait('Aardvark')
                        ->property('propertyOne')->type('Hello')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    trait Aardvark
                    {
                        public $eyes

                        /**
                         * @var Hello
                         */
                        public $propertyOne;
                    }
                    EOT
            ];

        yield 'trait: It adds a property before methods' => [
                <<<'EOT'
                    trait Aardvark
                    {
                        public function crawl()
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->trait('Aardvark')
                        ->property('propertyOne')->type('Hello')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    trait Aardvark
                    {
                        /**
                         * @var Hello
                         */
                        public $propertyOne;

                        public function crawl()
                        {
                        }
                    }
                    EOT
            ];
    }

    /**
     * @dataProvider provideMethods
     */
    public function testMethods(string $existingCode, SourceCode $prototype, string $expectedCode): void
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideMethods()
    {
        //yield 'It adds a method' => [
                //<<<'EOT'
                    //class Aardvark
                    //{
                    //}
                    //EOT
                //, SourceCodeBuilder::create()
                    //->class('Aardvark')
                        //->method('methodOne')->end()
                    //->end()
                    //->build(),
                //<<<'EOT'
                    //class Aardvark
                    //{
                        //public function methodOne()
                        //{
                        //}
                    //}
                    //EOT
            //];

        //yield 'It adds multiple methods' => [
                //<<<'EOT'
                    //class Aardvark
                    //{
                    //}
                    //EOT
                //, SourceCodeBuilder::create()
                    //->class('Aardvark')
                        //->method('methodOne')->end()
                        //->method('methodTwo')->end()
                    //->end()
                    //->build(),
                //<<<'EOT'
                    //class Aardvark
                    //{
                        //public function methodOne()
                        //{
                        //}

                        //public function methodTwo()
                        //{
                        //}
                    //}
                    //EOT
            //];

        //yield 'It generates a constructor' => [
            //<<<'EOT'
            //EOT,
            //SourceCodeBuilder::create()
            //->class('Foo')
                //->method('__construct')
                    //->parameter('config')
                        //->type('int')
                    //->end()
                //->end()
            //->end()
            //->build(),
            //<<<'EOT'

            //class Foo
            //{
                //public function __construct(int $config)
                //{
                //}
            //}
            //EOT
        //];

        yield 'It generates a constructor with promoted properties' => [
            <<<'EOT'
            class Foo
            {

            }
            EOT,
            SourceCodeBuilder::create()
            ->class('Foo')
                ->method('__construct')
                    ->parameter('config')
                        ->type('int')
                        ->visibility(Visibility::private())
                    ->end()
                ->end()
            ->end()
            ->build(),
            <<<'EOT'

            class Foo
            {
                public function __construct(private int $config)
                {
                }
            }
            EOT
        ];

        return;
        yield 'It adds parameterized method' => [
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')
                            ->parameter('sniff')
                                ->type('Snort')
                            ->end()
                        ->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function methodOne(Snort $sniff)
                        {
                        }
                    }
                    EOT
        ];

        yield 'It adds parameterized method with array shape' => [
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')
                            ->parameter('sniff')
                                ->asVariadic()
                                ->type('Snort')
                            ->end()
                        ->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function methodOne(Snort ...$sniff)
                        {
                        }
                    }
                    EOT
            ];

        yield 'It is idempotent' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function methodOne()
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function methodOne()
                        {
                        }
                    }
                    EOT
            ];

        yield 'It adds a method after existing methods' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                        }

                        public function nose()
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                        }

                        public function nose()
                        {
                        }

                        public function methodOne()
                        {
                        }
                    }
                    EOT
            ];

        yield 'It adds a documented methods' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')->docblock('Hello')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                        }

                        /**
                         * Hello
                         */
                        public function methodOne()
                        {
                        }
                    }
                    EOT
            ];

        yield 'It adds a method with a body' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')->body()->line('echo "Hello World";')->end()->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                        }

                        public function methodOne()
                        {
                            echo "Hello World";
                        }
                    }
                    EOT
            ];

        yield 'Add line to a methods body' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('eyes')->body()->line('echo "Hello World";')->end()->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                            echo "Hello World";
                        }
                    }
                    EOT
            ];

        yield 'Add lines after existing lines' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                            echo "Goodbye world!";
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('eyes')->body()->line('echo "Hello World";')->end()->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                            echo "Goodbye world!";
                            echo "Hello World";
                        }
                    }
                    EOT
            ];

        yield 'Should not add the same line twice' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                            echo "Hello World";
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('eyes')->body()->line('echo "Hello World";')->end()->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function eyes()
                        {
                            echo "Hello World";
                        }
                    }
                    EOT
            ];

        yield 'It does not modify existing methods 1' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function hello(
                            array $foobar = []
                        )
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('hello')->parameter('foobar')->type('array')->defaultValue([])->end()->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function hello(
                            array $foobar = []
                        )
                        {
                        }
                    }
                    EOT
            ];

        yield 'It modifies parameter types' => [
                <<<'EOT'
                class Foo {
                    public function changeMe(OldClass $dependency)
                    {
                    }
                }
                EOT,
                SourceCodeBuilder::create()
                    ->class('Foo')
                        ->method('changeMe')
                            ->parameter('dependency')
                                ->type('SomeOtherClass')
                            ->end()
                        ->end()
                    ->end()
                    ->build()
                ,
                <<<'EOT'
                class Foo {
                    public function changeMe(SomeOtherClass $dependency)
                    {
                    }
                }
                EOT
        ];

        yield 'It does not modify existing methods with imported names' => [
                <<<'EOT'

                    use Barfoo as Foobar;

                    class Aardvark
                    {
                        public function hello(Foobar $foobar): Foobar
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('hello')->parameter('foobar')->type('Barfoo')->end()->returnType('Barfoo')->end()
                    ->end()
                    ->build(),
                <<<'EOT'

                    use Barfoo as Foobar;

                    class Aardvark
                    {
                        public function hello(Foobar $foobar): Foobar
                        {
                        }
                    }
                    EOT
            ];

        yield 'It modifies the return type' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function hello(): Foobar
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('hello')->returnType('Barfoo')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function hello(): Barfoo
                        {
                        }
                    }
                    EOT
            ];
    }

    /**
     * @dataProvider provideConstants
     */
    public function testConstants(string $existingCode, SourceCode $prototype, string $expectedCode): void
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideConstants()
    {
        yield 'It adds a constant' => [
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->constant('constantOne', 'foo')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        const constantOne = 'foo';
                    }
                    EOT
            ];

        yield 'It adds is idempotent' => [
                <<<'EOT'
                    class Aardvark
                    {
                        const constantOne = 'aaa';
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->constant('constantOne', 'aaa')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        const constantOne = 'aaa';
                    }
                    EOT
            ];

        yield 'It adds a constant after existing constants' => [
                <<<'EOT'
                    class Aardvark
                    {
                        const constantOne = 'aaa';
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->constant('constantTwo', 'bbb')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        const constantOne = 'aaa';
                        const constantTwo = 'bbb';
                    }
                    EOT
            ];

        yield 'It adds multiple constants' => [
                <<<'EOT'
                    class Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->constant('constantOne', 'a')->end()->constant('constantTwo', 'b')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        const constantOne = 'a';
                        const constantTwo = 'b';
                    }
                    EOT
            ];

        yield 'It adds before methods' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function crawl()
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                    ->constant('constantOne', 1)->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        const constantOne = 1;

                        public function crawl()
                        {
                        }
                    }
                    EOT
            ];

        yield 'It adds before properties' => [
                <<<'EOT'
                    class Aardvark
                    {
                        private $crawlSpace;
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                    ->constant('constantOne', 1)->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        const constantOne = 1;

                        private $crawlSpace;
                    }
                    EOT
        ];
    }

    /**
     * @dataProvider provideInterfaces
     */
    public function testInterfaces(string $existingCode, SourceCode $prototype, string $expectedCode): void
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideInterfaces()
    {
        yield 'It adds an interface' => [

                <<<'EOT'
                    EOT
                , SourceCodeBuilder::create()->interface('Aardvark')->end()->build(),
                <<<'EOT'

                    interface Aardvark
                    {
                    }
                    EOT
            ];

        yield 'It adds an interface in a namespace' => [

                <<<'EOT'
                    namespace Foobar;
                    EOT
                , SourceCodeBuilder::create()->interface('Aardvark')->end()->build(),
                <<<'EOT'
                    namespace Foobar;

                    interface Aardvark
                    {
                    }
                    EOT
            ];

        yield 'It adds methods to an interface' => [

                <<<'EOT'
                    interface Aardvark
                    {
                    }
                    EOT
                , SourceCodeBuilder::create()->interface('Aardvark')->method('foo')->end()->end()->build(),
                <<<'EOT'
                    interface Aardvark
                    {
                        public function foo();
                    }
                    EOT
            ];
    }

    public function provideMethodParameters()
    {
        yield 'It adds parameters' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function methodOne()
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')
                            ->parameter('sniff')
                                ->type('Barf')
                            ->end()
                        ->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function methodOne(Barf $sniff)
                        {
                        }
                    }
                    EOT
            ];

        yield 'It adds nullable typed parameters' => [
                <<<'EOT'
                    class Aardvark
                    {
                        public function methodOne()
                        {
                        }
                    }
                    EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')
                            ->parameter('sniff')->type(
                                TypeFactory::fromString('?Barf')
                            )
                            ->end()
                        ->end()
                    ->end()
                    ->build(),
                <<<'EOT'
                    class Aardvark
                    {
                        public function methodOne(?Barf $sniff)
                        {
                        }
                    }
                    EOT
            ];
    }

    abstract protected function updater(): Updater;

    private function assertUpdate(string $existingCode, SourceCode $prototype, string $expectedCode): void
    {
        $existingCode = '<?php'.PHP_EOL.$existingCode;
        $edits = $this->updater()->textEditsFor($prototype, Code::fromString($existingCode));
        $this->assertEquals('<?php' . PHP_EOL . $expectedCode, $edits->apply($existingCode));
    }
}
