<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Builder;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Builder\ConstructorBuilder;
use Phpactor\CodeBuilder\Domain\Builder\ConstructorParameterBuilder;
use Phpactor\CodeBuilder\Domain\Builder\Exception\InvalidBuilderException;
use Phpactor\CodeBuilder\Domain\Builder\NamedBuilder;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;

class MethodBuilderTest extends TestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    public function testExceptionOnAddNonParameterBuilder(): void
    {
        $this->expectException(InvalidBuilderException::class);
        $builder = $this->prophesize(NamedBuilder::class);
        SourceCodeBuilder::create()
            ->class('One')
            ->method('two')
            ->add($builder->reveal());
    }

    public function testBuildingAConstructor(): void
    {
        $methodBuilder = SourceCodeBuilder::create() ->class('One') ->method('__construct');
        $property = $methodBuilder->parameter('someParam');

        $this->assertInstanceOf(ConstructorBuilder::class, $methodBuilder);
        $this->assertInstanceOf(ConstructorParameterBuilder::class, $property);
    }
}
