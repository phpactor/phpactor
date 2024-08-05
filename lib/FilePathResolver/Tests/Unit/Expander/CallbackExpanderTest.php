<?php

namespace Phpactor\FilePathResolver\Tests\Unit\Expander;

use Phpactor\FilePathResolver\Expander;
use Phpactor\FilePathResolver\Expander\CallbackExpander;
use RuntimeException;

class CallbackExpanderTest extends ExpanderTestCase
{
    public function createExpander(): Expander
    {
        return new CallbackExpander('foo', function () {
            return 'bar';
        });
    }

    public function testExpandsCallbackValue(): void
    {
        $this->assertEquals('bar', $this->expand('%foo%'));
    }

    public function testThrowsExceptionWhenCallbackReturnsNonString(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Closure in callback expander');

        (new CallbackExpander('foo', function () {
            return 123;
        }))->replacementValue();
    }
}
