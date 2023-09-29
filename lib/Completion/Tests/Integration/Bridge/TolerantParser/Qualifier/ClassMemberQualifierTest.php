<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\Qualifier;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\ClassMemberQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Closure;

class ClassMemberQualifierTest extends TolerantQualifierTestCase
{
    /**
     * @return Generator<string,array{string,(Closure(Node|null): void)}>
     */
    public function provideCouldComplete(): Generator
    {
        yield 'non member access' => [
            '<?php $hello<>',
            function (?Node $node): void {
                $this->assertNull($node);
            }
        ];

        yield 'variable with previous accessor' => [
            '<?php $foobar->hello; $hello<>',
            function (?Node $node): void {
                $this->assertNull($node);
            }

        ];

        yield 'statement with previous member access' => [
            '<?php if ($foobar && $this->foobar) { echo<>',
            function (?Node $node): void {
                $this->assertNull($node);
            }
        ];

        yield 'variable with previous static member access' => [
            '<?php Hello::hello(); $foo<>',
            function (?Node $node): void {
                $this->assertNull($node);
            }
        ];

        yield 'returns the scoped property access expression' => [
            '<?php Hello::<>',
            function (?Node $node): void {
                self::assertInstanceOf(ScopedPropertyAccessExpression::class, $node);
            }
        ];

        yield 'returns the scoped property access expression parent' => [
            '<?php Hello::FO<>',
            function (?Node $node): void {
                $this->assertInstanceOf(ScopedPropertyAccessExpression::class, $node);
            }
        ];
    }

    public function createQualifier(): TolerantQualifier
    {
        return new ClassMemberQualifier();
    }
}
