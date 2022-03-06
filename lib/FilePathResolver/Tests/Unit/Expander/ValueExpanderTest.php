<?php

namespace Phpactor\FilePathResolver\Tests\Unit\Expander;

use Phpactor\FilePathResolver\Expander;
use Phpactor\FilePathResolver\Expander\ValueExpander;

class ValueExpanderTest extends ExpanderTestCase
{
    public function createExpander(): Expander
    {
        return new ValueExpander('test', 'value');
    }

    public function testExpandsValue(): void
    {
        $this->assertEquals('/foo/value/bar', $this->expand('/foo/%test%/bar'));
    }
}
