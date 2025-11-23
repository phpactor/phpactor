<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Builder;

use Prophecy\PhpUnit\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Builder\Exception\InvalidBuilderException;
use Phpactor\CodeBuilder\Domain\Builder\NamedBuilder;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;

class MethodBuilderTest extends TestCase
{
    use ProphecyTrait;

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
        $methodBuilder->parameter('config')->visibility(Visibility::public());

        $result = $methodBuilder->build();
        $this->assertSame((string) Visibility::public(), (string) $result->parameters()->first()->visibility());
    }

    public function testNoVisibilityForNormalMethods(): void
    {
        $this->expectExceptionMessage('Only constructors can have parameters with visibility. Current function: doStuff');
        $methodBuilder = SourceCodeBuilder::create()->class('One')->method('doStuff')
            ->parameter('config')->visibility(Visibility::public());
    }
}
