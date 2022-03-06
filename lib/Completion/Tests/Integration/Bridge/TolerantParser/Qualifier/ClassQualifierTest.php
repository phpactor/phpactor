<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\Qualifier;

use Generator;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\ClassQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;

class ClassQualifierTest extends TolerantQualifierTestCase
{
    public function provideCouldComplete(): Generator
    {
        yield 'non member access' => [
            '<?php $hello<>',
            function ($node): void {
                $this->assertNull($node);
            }
        ];
        yield 'variable with previous accessor' => [
            '<?php $foobar->hello; $hello<>',
            function ($node): void {
                $this->assertNull($node);
            }
        ];
        yield 'statement with previous member access' => [
            '<?php if ($foobar && $this->foobar) { echo<>',
            function ($node): void {
                $this->assertNull($node);
            }
        ];
        yield 'variable with previous static member access' => [
            '<?php Hello::hello(); $foo<>',
            function ($node): void {
                $this->assertNull($node);
            }
        ];
    }

    public function createQualifier(): TolerantQualifier
    {
        return new ClassQualifier();
    }
}
