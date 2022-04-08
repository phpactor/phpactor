<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Generator;

class ForeachWalkerTest extends FrameWalkerTestCase
{
    public function provideWalk(): Generator
    {
        yield 'Assigns type to foreach item' => [
            <<<'EOT'
                <?php
                /** @var int[] $items */
                $items = [1, 2, 3, 4];

                foreach ($items as $item) {
                <>
                }
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(2, $frame->locals());
                $this->assertCount(1, $frame->locals()->byName('item'));
                $this->assertEquals('int', (string) $frame->locals()->byName('item')->first()->type());
            }
        ];

        yield 'yields array keys' => [
            <<<'EOT'
                <?php
                /** @var int[] $items */
                $items = [ 'one' => 1, 'two' => 2 ];

                foreach ($items as $key => $item) {
                <>
                }
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(3, $frame->locals());
                $this->assertCount(1, $frame->locals()->byName('key'));
                $this->assertEquals(TypeFactory::unknown(), $frame->locals()->byName('key')->first()->type());
                $this->assertEquals(false, $frame->locals()->byName('key')->first()->isProperty());
            }
        ];


        yield 'Assigns fully qualfied type to foreach item' => [
            <<<'EOT'
                <?php

                namespace Foobar;

                /** @var Barfoo[] $items */
                $items = [];

                foreach ($items as $item) {
                <>
                }
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(2, $frame->locals());
                $this->assertCount(1, $frame->locals()->byName('item'));
                $this->assertEquals('Foobar\\Barfoo', (string) $frame->locals()->byName('item')->first()->type());
            }
        ];

        yield 'Assigns fully qualfied type to foreach from collection' => [
            <<<'EOT'
                <?php

                namespace Foobar;

                /**
                 * @template T
                 * @implements \Iterator<T>
                 */
                class Collection implements \Iterator {
                }

                /** @var Collection<Item> $items */
                $items = new Collection();

                foreach ($items as $item) {
                <>
                }
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(2, $frame->locals());
                $this->assertCount(1, $frame->locals()->byName('item'));
                $this->assertEquals(
                    'Foobar\\Collection<Foobar\Item>',
                    (string) $frame->locals()->byName('items')->first()->type()
                );
                $this->assertEquals(
                    'Foobar\\Item',
                    (string) $frame->locals()->byName('item')->first()->type()
                );
            }
        ];

        yield 'It returns type for a foreach member (with a docblock)' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        /** @var Foobar $foobar */
                        foreach ($collection as $foobar) {
                            $foobar->foobar();
                            <>
                        }
                    }
                }
                EOT
        , function (Frame $frame): void {
            $vars = $frame->locals()->byName('foobar');
            $this->assertCount(2, $vars);
            $var = $vars->atIndex(1);
            $this->assertEquals('Foobar', (string) $var->type());
        }];

        yield 'List assignment in foreach' => [
            <<<'EOT'
                <?php
                foreach (['foo', 'bar'] as [ $foo, $bar ]) {
                    <>
                }
                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(2, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->atIndex(0)->name());
                $this->assertEquals('bar', $frame->locals()->atIndex(1)->name());
                $this->assertEquals(
                    '"foo"',
                    (string)$frame->locals()->byName('foo')->first()->type()
                );
                $this->assertEquals(
                    '"bar"',
                    (string)$frame->locals()->byName('bar')->first()->type()
                );
            }
        ];

        yield 'Typed array' => [
            <<<'EOT'
                <?php
                $vars = ['one', 'two'];

                foreach ($vars as [ $foo, $bar ]) {
                    <>
                }
                EOT
        ,
            function (Frame $frame): void {
                $this->assertEquals(
                    '"one"',
                    (string)$frame->locals()->byName('foo')->first()->type(),
                    'Value'
                );
            }
        ];
    }
}
