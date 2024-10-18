<?php

namespace Phpactor\FilePathResolver\Tests\Unit\Expander;

use Phpactor\FilePathResolver\Expander;
use Phpactor\FilePathResolver\Expander\CallbackExpander;

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
}
