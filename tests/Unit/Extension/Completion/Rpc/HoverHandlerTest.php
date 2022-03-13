<?php

namespace Phpactor\Tests\Unit\Extension\Completion\Rpc;

use Phpactor\Completion\Bridge\WorseReflection\Formatter\ClassFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\MethodFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\VariableFormatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Extension\CompletionExtra\Rpc\HoverHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;

class HoverHandlerTest extends HandlerTestCase
{
    private Reflector $reflector;

    public function setUp(): void
    {
        $this->reflector = ReflectorBuilder::create()->enableContextualSourceLocation()->build();
        $this->formatter = new ObjectFormatter([]);
    }

    /**
     * @dataProvider provideHover
     */
    public function testHover(string $source, string $expectedMessage): void
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
            'method foobar'
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

    /**
     * @dataProvider provideHoverWithFormatter
     */
    public function testHoverWithFormatter(string $source, string $expectedMessage): void
    {
        $this->formatter = new ObjectFormatter([
            new MethodFormatter(),
            new ClassFormatter(),
            new VariableFormatter(),
        ]);

        [ $source, $offset ] = ExtractOffset::fromSource($source);

        $response = $this->handle(HoverHandler::NAME, [
            'source' => $source,
            'offset' => $offset,
        ]);

        $this->assertEquals($expectedMessage, $response->message());
    }

    public function provideHoverWithFormatter()
    {
        yield 'method' => [
            '<?php class Foobar { public function fo<>obar() { } }',
            'pub foobar()',
        ];


        yield 'method with documentation' => [
            <<<'EOT'
                <?php 

                class Foobar { 
                    /**
                     * this is documentation
                     */
                public function fo<>obar() { } 
                }
                EOT
            ,
            <<<'EOT'
                pub foobar()
                EOT
        ];

        yield 'class with documentation' => [
            <<<'EOT'
                <?php 

                /**
                 * this is documentation
                 */
                class F<>oobar {}
                EOT
            ,
            <<<'EOT'
                Foobar
                EOT
        ];

        yield 'unknown' => [
            '<?php <> $foo = "bar"',
            '<unknown> <unknown>',
        ];
    }

    protected function createHandler(): Handler
    {
        return new HoverHandler($this->reflector, $this->formatter);
    }
}
