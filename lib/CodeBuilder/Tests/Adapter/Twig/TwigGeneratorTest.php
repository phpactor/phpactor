<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\Twig;

use PHPUnit\Framework\Attributes\TestDox;
use Phpactor\CodeBuilder\Tests\Adapter\GeneratorTestCase;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;

class TwigGeneratorTest extends GeneratorTestCase
{
    #[TestDox('It should fallback to the default templates if variant template
         does not exist.')]
    public function testFallback(): void
    {
        $builder = SourceCodeBuilder::create();
        $source = $this->renderer()->render($builder->build(), 'unknown');
        $this->assertEquals('<?php', (string) $source);
    }

    protected function renderer(): Renderer
    {
        static $generator;

        if ($generator) {
            return $generator;
        }

        $generator = new TwigRenderer();

        return $generator;
    }
}
