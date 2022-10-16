<?php

namespace Phpactor\Extension\Symfony\Tests\Integration\Completor;

use Closure;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\Symfony\Completor\SymfonyContainerCompletor;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class SymfonyContainerCompletorTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, string $containerXml, Closure $assertion): void
    {
        [$source, $start] = ExtractOffset::fromSource($source);
        $node = (new Parser())->parseSourceFile($source)->getDescendantNodeAtPosition((int)$start);
        $suggestions = iterator_to_array($this->completor()->complete(
            $node,
            TextDocumentBuilder::create($source)->language('php')->build(),
            ByteOffset::fromInt((int)$start)
        ));
        $assertion($suggestions);
    }

    /**
     * @return Generator<string,array{string,string,Closure(Suggestion[]):void}>
     */
    public function provideComplete(): Generator
    {
        yield 'all' => [
            <<<'EOT'
            <?php

            use Symfony\Component\DependencyInjection\Container;
            $container = new Container();
            $foobar = $container->get('foobar');
            EOT
            ,
            <<<'EOT'
            <?xml version="1.0" encoding="utf-8"?>
            <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
              <services>
                <service id="service_container" class="Symfony\Component\DependencyInjection\ContainerInterface" public="true" synthetic="true"/>
              </services>
            </container>
            EOT
            ,
            /** @param Suggestion[] $suggestions */
            function (array $suggestions): void
            {
                dump($suggestions);
            }
        ];
    }

    private function completor(): TolerantCompletor
    {
        return new SymfonyContainerCompletor();
    }
}
