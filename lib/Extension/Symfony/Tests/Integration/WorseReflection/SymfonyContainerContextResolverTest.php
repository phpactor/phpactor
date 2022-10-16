<?php

namespace Phpactor\Extension\Symfony\Tests\Integration\WorseReflection;

use Phpactor\Extension\Symfony\Tests\IntegrationTestCase;
use Phpactor\Extension\Symfony\WorseReflection\SymfonyContainerContextResolver;
use Phpactor\WorseReflection\Core\Inference\Walker\TestAssertWalker;
use Phpactor\WorseReflection\ReflectorBuilder;

class SymfonyContainerContextResolverTest extends IntegrationTestCase
{
    public function testResolver(): void
    {
        $reflector = ReflectorBuilder::create()
            ->addFrameWalker(new TestAssertWalker($this))
            ->addSource(
                '<?php namespace Symfony\Component\DependencyInjection { interface ContainerInterface{} class Container implements ContainerInterface{}}'
            )
            ->addMemberContextResolver(new SymfonyContainerContextResolver())
            ->build();

        $sourceCode = <<<'EOT'
        <?php
        use Symfony\Component\DependencyInjection\ContainerInterface;
        function (ContainerInterface $container) {
            $foo = $container->get('foo.bar');
            wrAssertType('Foo\Bar', $foo);
        }
        EOT
        ;

        $reflector->reflectOffset($sourceCode, mb_strlen($sourceCode));
    }
}
