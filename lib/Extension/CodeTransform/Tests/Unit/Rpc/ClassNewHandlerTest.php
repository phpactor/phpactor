<?php

namespace Phpactor\Extension\CodeTransform\Tests\Unit\Rpc;

use Phpactor\ClassFileConverter\Domain\ClassName as ConvertedClassName;
use Phpactor\ClassFileConverter\Domain\ClassNameCandidates;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\GenerateNew;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\CodeTransform\Rpc\ClassInflectHandler;
use Phpactor\Extension\CodeTransform\Rpc\ClassNewHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ClassNewHandlerTest extends AbstractClassGenerateHandler
{
    use ProphecyTrait;

    private ObjectProphecy $generator;

    public function setUp(): void
    {
        parent::setUp();
        $this->generator = $this->prophesize(GenerateNew::class);
    }

    public function createHandler(): Handler
    {
        return new ClassNewHandler(
            new Generators([
                'one' => $this->generator->reveal()
            ]),
            $this->fileToClass->reveal()
        );
    }

    public function testGeneratesNewClass(): void
    {
        $this->fileToClass->fileToClassCandidates(
            FilePath::fromString($this->exampleNewPath())
        )->willReturn(ClassNameCandidates::fromClassNames([
            $class1 = ConvertedClassName::fromString(self::EXAMPLE_CLASS_1)
        ]));

        $this->generator->generateNew(
            ClassName::fromString(self::EXAMPLE_CLASS_1)
        )->willReturn(
            SourceCode::fromStringAndPath('<?php', $this->exampleNewPath())
        );

        $response = $this->createTester()->handle(ClassNewHandler::NAME, [
            ClassInflectHandler::PARAM_CURRENT_PATH => self::EXAMPLE_PATH,
            ClassInflectHandler::PARAM_NEW_PATH => $this->exampleNewPath(),
            ClassInflectHandler::PARAM_VARIANT => self::EXAMPLE_VARIANT,
        ]);

        $this->assertInstanceOf(ReplaceFileSourceResponse::class, $response);
        $this->assertEquals($this->exampleNewPath(), $response->path());
        $this->assertFileExists($this->exampleNewPath());
    }
}
