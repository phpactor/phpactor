<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\LspCommand;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassFileConverter\Domain\ClassName as PhpactorClassName;
use Phpactor\ClassFileConverter\Domain\ClassNameCandidates;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\GenerateNew;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\CreateClassCommand;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use function Amp\Promise\wait;

class CreateClassCommandTest extends TestCase
{
    const EXAMPLE_VARIANT = 'test_transform';

    public function testAppliesTransform(): void
    {
        [$tester, $watcher] = $this->createTester();
        $tester->textDocument()->open('file:///foobar', 'foobar');
        $promise = $tester->workspace()->executeCommand('create_class', [
            'file:///foobar',
            self::EXAMPLE_VARIANT
        ]);
        $watcher->resolveLastResponse(new ApplyWorkspaceEditResult(true));
        $response = wait($promise);
        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertInstanceOf(ApplyWorkspaceEditResult::class, $response->result);
    }

    public function testAppliesTransformForNonExistingClass(): void
    {
        [$tester, $watcher] = $this->createTester();
        $promise = $tester->workspace()->executeCommand('create_class', [
            'file:///foobar',
            self::EXAMPLE_VARIANT
        ]);
        $watcher->resolveLastResponse(new ApplyWorkspaceEditResult(true));
        $response = wait($promise);
        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertInstanceOf(ApplyWorkspaceEditResult::class, $response->result);
    }

    /**
     * @return array{LanguageServerTester,TestResponseWatcher}
     */
    private function createTester(): array
    {
        $generator = new TestGenerator();
        $generators = new Generators([
            self::EXAMPLE_VARIANT => $generator
        ]);
        $fileToClass = new TestFileToClass();
        $tester = LanguageServerTesterBuilder::create();
        $tester->addCommand('create_class', new CreateClassCommand(
            $tester->clientApi(),
            $tester->workspace(),
            $generators,
            $fileToClass
        ));
        $watcher = $tester->responseWatcher();
        $tester = $tester->build();
        return [$tester, $watcher];
    }
}

class TestGenerator implements GenerateNew
{
    public const EXAMPLE_TEXT = 'hello';
    public const EXAMPLE_PATH = '/path';


    public function generateNew(ClassName $targetName): SourceCode
    {
        return SourceCode::fromStringAndPath(self::EXAMPLE_TEXT, self::EXAMPLE_PATH);
    }
}

class TestFileToClass implements FileToClass
{
    public const TEST_CLASS_NAME = 'Foobar';

    public function fileToClassCandidates(FilePath $filePath): ClassNameCandidates
    {
        return ClassNameCandidates::fromClassNames([
            PhpactorClassName::fromString(self::TEST_CLASS_NAME)
        ]);
    }
}
