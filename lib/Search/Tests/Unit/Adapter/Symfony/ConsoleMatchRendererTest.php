<?php

namespace Phpactor\Search\Tests\Unit\Adapter\Symfony;

use Generator;
use Phpactor\Search\Adapter\Symfony\ConsoleMatchRenderer;
use Phpactor\Search\Adapter\TolerantParser\TolerantMatchFinder;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleMatchRendererTest extends TestCase
{
    /**
     * @dataProvider provideRender
     */
    public function testRender(string $document, string $template, string $toContain): void
    {
        $document = TextDocumentBuilder::create($document)->uri('file:///foobar/file.php')->build();
        $matches = TolerantMatchFinder::createDefault()->match($document, $template);
        $output = new BufferedOutput(32, false);
        $renderer = new ConsoleMatchRenderer($output, __DIR__);
        $renderer->render($matches);
        $fetch = $output->fetch();
        self::assertStringContainsString($toContain, $fetch);
    }

    /**
     * @return Generator<string,array{string,string,string}>
     */
    public function provideRender(): Generator
    {
        yield 'no match' => [
            '<?php class Foobar {}',
            'class Baaaa {}',
            '',
        ];
        yield 'no placeholder' => [
            '<?php class Foobar {}',
            'class Foobar {}',
            'class Foobar {}',
        ];
        yield 'placeholder' => [
            '<?php class Foobar {}',
            'class __A__ {}',
            'class Foobar {}',
        ];
        yield '4 placeholder' => [
            '<?php class Foobar { public function foo(Foo $foobar){}}',
            'class __A__ {public function __B__(__C__ $__D__){}}',
            'public function foo',
        ];
    }
}
