<?php

namespace Phpactor\FilePathResolver\Tests\Unit;

use Phpactor\FilePathResolver\Exception\UnknownToken;
use Phpactor\FilePathResolver\Expander\ValueExpander;
use Phpactor\FilePathResolver\Expanders;
use PHPUnit\Framework\TestCase;

class ExpandersTest extends TestCase
{
    public function testProvidesArrayRepresentation(): void
    {
        $expanders = new Expanders([
            new ValueExpander('foo', 'bar'),
            new ValueExpander('bar', 'foo'),
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'bar' => 'foo',
        ], $expanders->toArray());
    }

    public function testThrowsExceptionIfUnknownTokenFound(): void
    {
        $this->expectException(UnknownToken::class);
        $expanders = new Expanders([
            new ValueExpander('foo', 'bar'),
            new ValueExpander('bar', 'foo'),
        ]);

        $expanders->get('baz');
    }
}
