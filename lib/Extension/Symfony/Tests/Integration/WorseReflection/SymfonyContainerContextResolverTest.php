<?php

namespace Phpactor\Extension\Symfony\Tests\Integration\WorseReflection;

use Phpactor\Extension\Symfony\Model\InMemorySymfonyContainerInspector;
use Phpactor\Extension\Symfony\Model\SymfonyContainerService;
use Phpactor\Extension\Symfony\Tests\IntegrationTestCase;
use Phpactor\Extension\Symfony\WorseReflection\SymfonyContainerContextResolver;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Inference\Walker\TestAssertWalker;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\ReflectorBuilder;

class SymfonyContainerContextResolverTest extends IntegrationTestCase
{
    public function testResolveStringLiteralId(): void
    {
        $this->resolve(
            <<<'EOT'
                <?php
                use Symfony\Component\DependencyInjection\ContainerInterface;
                function (ContainerInterface $container) {
                    $foo = $container->get('foo.bar');
                    wrAssertType('Foo\Bar', $foo);
                }
                EOT
            ,
            [
                new SymfonyContainerService('foo.bar', TypeFactory::class('Foo\Bar')),
            ]
        );
    }

    public function testResolveStringLiteralIdNoMatches(): void
    {
        $this->resolve(
            <<<'EOT'
                <?php
                use Symfony\Component\DependencyInjection\ContainerInterface;
                function (ContainerInterface $container) {
                    $foo = $container->get(Foo::class);
                    wrAssertType('Foo\Bar', $foo);
                }
                EOT
            ,
            [
                new SymfonyContainerService('Foo', TypeFactory::class('Foo\Bar')),
            ]
        );
    }

    /**
     * @param SymfonyContainerService[] $services
     */
    public function resolve(string $sourceCode, array $services): void
    {
        $sourceCode = TextDocumentBuilder::fromUnknown($sourceCode);
        $reflector = ReflectorBuilder::create()
            ->addFrameWalker(new TestAssertWalker($this))
            ->addSource(
                '<?php namespace Symfony\Component\DependencyInjection { interface ContainerInterface{public function get(string $id);} class Container implements ContainerInterface{}}'
            )
                ->addMemberContextResolver(new SymfonyContainerContextResolver(
                    new InMemorySymfonyContainerInspector($services, [])
                ))
            ->build();

        $reflector->reflectOffset($sourceCode, mb_strlen($sourceCode));
    }
}
