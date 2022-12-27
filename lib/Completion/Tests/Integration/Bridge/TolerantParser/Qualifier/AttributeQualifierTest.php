<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\Qualifier;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\AttributeGroup;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\AttributeQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;

class AttributeQualifierTest extends TolerantQualifierTestCase
{
    /**
     * @return Generator<string,array{string,(\Closure(Node|null): void)}>
     */
    public function provideCouldComplete(): Generator
    {
        yield 'not attribute' => [
            '<?php $hello<>',
            function (?Node $node): void {
                $this->assertNull($node);
            },
        ];

        yield 'in not mapped attribute' => [
            '<?php #[Att<>]',
            function (?Node $node): void {
                $this->assertInstanceOf(Attribute::class, $node);
            },
        ];

        yield 'in not mapped attribute, empty name' => [
            '<?php #[<>]',
            function (?Node $node): void {
                $this->assertInstanceOf(AttributeGroup::class, $node);
            },
        ];

        yield 'in not mapped attribute, empty name of the second' => [
            '<?php #[Attribute(), <>]',
            function (?Node $node): void {
                $this->assertInstanceOf(AttributeGroup::class, $node);
            },
        ];

        yield 'in method attribute' => [
            '<?php class X {#[Att<>] public function x()',
            function (?Node $node): void {
                $this->assertInstanceOf(Attribute::class, $node);
            },
        ];
    }

    public function createQualifier(): TolerantQualifier
    {
        return new AttributeQualifier();
    }
}
