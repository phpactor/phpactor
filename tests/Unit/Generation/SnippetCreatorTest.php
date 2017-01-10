<?php

namespace Phpactor\Tests\Unit\Generation;

use Phpactor\Generation\SnippetGeneratorRegistry;
use Phpactor\Generation\SnippetCreator;
use Phpactor\Generation\SnippetGeneratorInterface;
use Phpactor\CodeContext;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SnippetCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var mixed
     */
    private $registry;
    
    /**
     * @var SnippetCreator
     */
    private $creator;

    /**
     * @var SnippetGenerator
     */
    private $generator;

    public function setUp()
    {
        $this->registry = $this->prophesize(SnippetGeneratorRegistry::class);
        $this->creator = new SnippetCreator($this->registry->reveal());

        $this->generator = $this->prophesize(SnippetGeneratorInterface::class);
    }

    public function testCreate()
    {
        $expectedSnippet = 'some snippet';

        $context = CodeContext::create('path', 'sourcecode', 0);
        $this->registry->get('foobar')->willReturn($this->generator->reveal());
        $this->generator->configureOptions(Argument::type(OptionsResolver::class))->will(function ($args) {
            $args[0]->setDefault('foo', 'bar');
            $args[0]->setDefault('bar', 'bar');
        });

        $this->generator->generate($context, [
            'foo' => 'bar',
            'bar' => 'bar',
        ])->willReturn($expectedSnippet);

        $snippet = $this->creator->create($context, 'foobar', [ 'foo' => 'bar' ]);

        $this->assertEquals($expectedSnippet, $snippet);
    }
}
