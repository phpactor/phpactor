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
use Phpactor\Extension\Symfony\Model\InMemorySymfonyContainerInspector;
use Phpactor\Extension\Symfony\Model\SymfonyContainerParameter;
use Phpactor\Extension\Symfony\Model\SymfonyContainerService;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\ReflectorBuilder;

class SymfonyContainerCompletorTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @dataProvider provideComplete
     * @param SymfonyContainerService[] $services
     */
    public function testComplete(string $source, array $services, Closure $assertion): void
    {
        [$source, $start] = ExtractOffset::fromSource($source);
        $node = (new Parser())->parseSourceFile($source)->getDescendantNodeAtPosition((int)$start);
        $suggestions = iterator_to_array($this->completor($services, [])->complete(
            $node,
            TextDocumentBuilder::create($source)->language('php')->build(),
            ByteOffset::fromInt((int)$start)
        ));
        $assertion($suggestions);
    }

    /**
     * @return Generator<array-key,array{string,array<array-key,SymfonyContainerService>,Closure(Suggestion[]):void}>
     */
    public function provideComplete(): Generator
    {
        yield 'not on symfony container, get method' => [
            <<<'EOT'
            <?php

            use Phpactor\Component\DependencyInjection\Container;
            $container = new Container();
            $foobar = $container->get(<>);
            EOT
            ,
            [
                new SymfonyContainerService('foobar', TypeFactory::class('Foobar')),
                new SymfonyContainerService('foobar.barfoo', TypeFactory::class('Foobar\\Barfoo')),
            ]
            ,
            /** @param Suggestion[] $suggestions */
            function (array $suggestions): void
            {
                self::assertCount(0, $suggestions);
            }
        ];
        yield 'on container, not get method' => [
            <<<'EOT'
            <?php

            use Symfony\Component\DependencyInjection\Container;
            $container = new Container();
            $foobar = $container->set(<>);
            EOT
            ,
            [
                new SymfonyContainerService('foobar', TypeFactory::class('Foobar')),
                new SymfonyContainerService('foobar.barfoo', TypeFactory::class('Foobar\\Barfoo')),
            ]
            ,
            /** @param Suggestion[] $suggestions */
            function (array $suggestions): void
            {
                self::assertCount(0, $suggestions);
            }
        ];
        yield 'on container, no suggestions' => [
            <<<'EOT'
            <?php

            use Symfony\Component\DependencyInjection\Container;
            $container = new Container();
            $foobar = $container->get(<>);
            EOT
            ,
            [
            ]
            ,
            /** @param Suggestion[] $suggestions */
            function (array $suggestions): void
            {
                self::assertCount(0, $suggestions);
            }
        ];

        yield 'on container with suggestions' => [
            <<<'EOT'
            <?php

            use Symfony\Component\DependencyInjection\Container;
            $container = new Container();
            $foobar = $container->get(<>);
            EOT
            ,
            [
                new SymfonyContainerService('foobar', TypeFactory::class('Foobar')),
                new SymfonyContainerService('foobar.barfoo', TypeFactory::class('Foobar\\Barfoo')),
            ]
            ,
            /** @param Suggestion[] $suggestions */
            function (array $suggestions): void
            {
                self::assertCount(2, $suggestions);
            }
        ];
    }

    /**
     * @param SymfonyContainerService[] $services
     * @param SymfonyContainerParameter[] $parameters
     */
    private function completor(array $services, array $parameters): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()->addSource(
            '<?php namespace Symfony\Component\DependencyInjection { interface ContainerInterface{} class Container implements ContainerInterface{}}'
        )->build();
        return new SymfonyContainerCompletor($reflector, new InMemorySymfonyContainerInspector($services, $parameters));
    }
}
