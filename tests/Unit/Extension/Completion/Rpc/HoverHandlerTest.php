<?php

namespace Phpactor\Tests\Unit\Extension\Completion\Rpc;

use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Extension\CompletionExtra\Rpc\HoverHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\WorseReflection\ReflectorBuilder;

class HoverHandlerTest extends HandlerTestCase
{
    /**
     * @var Reflector
     */
    private $reflector;

    public function setUp()
    {
        $this->reflector = ReflectorBuilder::create()->enableContextualSourceLocation()->build();
        $this->formatter = new ObjectFormatter([]);
    }
    protected function createHandler(): Handler
    {
        return new HoverHandler($this->reflector, $this->formatter);
    }

    /**
     * @dataProvider provideHover
     */
    public function testHover(string $source, string $expectedMessage)
    {
        [ $source, $offset ] = ExtractOffset::fromSource($source);

        $response = $this->handle(HoverHandler::NAME, [
            'source' => $source,
            'offset' => $offset,
        ]);

        $this->assertEquals($expectedMessage, $response->message());
    }

    public function provideHover()
    {
        yield 'method' => [
            '<?php class Foobar { public function fo<>obar() { } }',
            'method foobar',
        ];

        yield 'property' => [
            '<?php class Foobar { private $fo<>obar; }',
            'property foobar',
        ];

        yield 'constant' => [
            '<?php class Foobar { const fo<>obar = 123; }',
            'constant foobar',
        ];

        yield 'class' => [
            '<?php c<>lass Foobar {}',
            'class Foobar',
        ];

        yield 'variable' => [
            '<?php $f<>oo = "bar"',
            'variable foo',
        ];

        yield 'unknown' => [
            '<?php <> $foo = "bar"',
            '<unknown> <unknown>',
        ];
    }
}
