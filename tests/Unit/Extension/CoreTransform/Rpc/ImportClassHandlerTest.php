<?php

namespace Phpactor\Tests\Unit\Extension\CoreTransform\Rpc;

use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Tests\Unit\Extension\Rpc\Handler\HandlerTestCase;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilestem\Application\ClassSearch;
use Phpactor\Extension\CodeTransform\Rpc\ImportClassHandler;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\ListInput;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\AliasAlreadyUsedException;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\ClassAlreadyImportedException;

class ImportClassHandlerTest extends HandlerTestCase
{
    const TEST_NAME = 'Foo';
    const TEST_OFFSET = 1234;
    const TEST_PATH = '/path/to';
    const TEST_SOURCE = '<?php foo';
    const TEST_ALIAS = 'Alias';

    /**
     * @var ObjectProphecy
     */
    private $importClass;

    /**
     * @var ObjectProphecy
     */
    private $classSearch;

    public function setUp()
    {
        $this->importClass = $this->prophesize(ImportClass::class);
        $this->classSearch = $this->prophesize(ClassSearch::class);
    }

    protected function createHandler(): Handler
    {
        return new ImportClassHandler(
            $this->importClass->reveal(),
            $this->classSearch->reveal(),
            'composer'
        );
    }

    public function testReturnsSuggestionsIfMultipleTargetsFound()
    {
        $this->classSearch->classSearch('composer', self::TEST_NAME)->willReturn([
            [
                'class' => 'Foobar',
            ],
            [
                'class' => 'Barfoo',
            ],
        ]);

        /** @var InputCallbackResponse $response */
        $response = $this->handle('import_class', [
            ImportClassHandler::PARAM_NAME => self::TEST_NAME,
            ImportClassHandler::PARAM_OFFSET => self::TEST_OFFSET,
            ImportClassHandler::PARAM_PATH => self::TEST_PATH,
            ImportClassHandler::PARAM_SOURCE => self::TEST_SOURCE
        ]);
        $this->assertInstanceOf(InputCallbackResponse::class, $response);
        $inputs = $response->inputs();
        $this->assertCount(1, $inputs);
        /** @var ListInput $input */
        $input = reset($inputs);
        $this->assertCount(2, $input->choices());
    }

    public function testShowsMessageIfNoClassesFound()
    {
        $this->classSearch->classSearch('composer', self::TEST_NAME)->willReturn([]);

        /** @var EchoResponse $response */
        $response = $this->handle('import_class', [
            ImportClassHandler::PARAM_NAME => self::TEST_NAME,
            ImportClassHandler::PARAM_OFFSET => self::TEST_OFFSET,
            ImportClassHandler::PARAM_PATH => self::TEST_PATH,
            ImportClassHandler::PARAM_SOURCE => self::TEST_SOURCE
        ]);
        $this->assertInstanceOf(EchoResponse::class, $response);
    }

    public function testImportsClassIfOnlyOneSuggestion()
    {
        $this->classSearch->classSearch('composer', self::TEST_NAME)->willReturn([
            [
                'class' => self::TEST_NAME
            ],
        ]);
        $transformed = SourceCode::fromStringAndPath('hello', self::TEST_PATH);
        $this->importClass->importClass(
            SourceCode::fromStringAndPath(self::TEST_SOURCE, self::TEST_PATH),
            self::TEST_OFFSET,
            self::TEST_NAME,
            null
        )->willReturn($transformed);

        /** @var EchoResponse $response */
        $response = $this->handle('import_class', [
            ImportClassHandler::PARAM_NAME => self::TEST_NAME,
            ImportClassHandler::PARAM_OFFSET => self::TEST_OFFSET,
            ImportClassHandler::PARAM_PATH => self::TEST_PATH,
            ImportClassHandler::PARAM_SOURCE => self::TEST_SOURCE
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $response);
    }

    public function testAsksForAliasIfClassAlreadyUsed()
    {
        $this->importClass->importClass(
            SourceCode::fromStringAndPath(self::TEST_SOURCE, self::TEST_PATH),
            self::TEST_OFFSET,
            self::TEST_NAME,
            null
        )->willThrow(new AliasAlreadyUsedException(self::TEST_ALIAS));

        /** @var EchoResponse $response */
        $response = $this->handle('import_class', [
            ImportClassHandler::PARAM_QUALIFIED_NAME => self::TEST_NAME,
            ImportClassHandler::PARAM_NAME => self::TEST_NAME,
            ImportClassHandler::PARAM_OFFSET => self::TEST_OFFSET,
            ImportClassHandler::PARAM_PATH => self::TEST_PATH,
            ImportClassHandler::PARAM_SOURCE => self::TEST_SOURCE
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $response);
        $inputs = $response->inputs();
        $this->assertCount(1, $inputs);
        /** @var TextInput $input */
        $input = reset($inputs);
        $this->assertInstanceOf(TextInput::class, $input);
    }

    public function testUsesGivenAlias()
    {
        $transformed = SourceCode::fromStringAndPath('hello', self::TEST_PATH);
        $this->importClass->importClass(
            SourceCode::fromStringAndPath(self::TEST_SOURCE, self::TEST_PATH),
            self::TEST_OFFSET,
            self::TEST_NAME,
            self::TEST_ALIAS
        )->willReturn($transformed);

        /** @var EchoResponse $response */
        $response = $this->handle('import_class', [
            ImportClassHandler::PARAM_ALIAS => self::TEST_ALIAS,
            ImportClassHandler::PARAM_QUALIFIED_NAME => self::TEST_NAME,
            ImportClassHandler::PARAM_NAME => self::TEST_NAME,
            ImportClassHandler::PARAM_OFFSET => self::TEST_OFFSET,
            ImportClassHandler::PARAM_PATH => self::TEST_PATH,
            ImportClassHandler::PARAM_SOURCE => self::TEST_SOURCE
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $response);
    }

    public function testShowsMessageIfSelectedClassIsAlreadyImported()
    {
        $this->importClass->importClass(
            SourceCode::fromStringAndPath(self::TEST_SOURCE, self::TEST_PATH),
            self::TEST_OFFSET,
            self::TEST_NAME,
            null
        )->willThrow(new ClassAlreadyImportedException(self::TEST_NAME, self::TEST_NAME));

        /** @var EchoResponse $response */
        $response = $this->handle('import_class', [
            ImportClassHandler::PARAM_QUALIFIED_NAME => self::TEST_NAME,
            ImportClassHandler::PARAM_NAME => self::TEST_NAME,
            ImportClassHandler::PARAM_OFFSET => self::TEST_OFFSET,
            ImportClassHandler::PARAM_PATH => self::TEST_PATH,
            ImportClassHandler::PARAM_SOURCE => self::TEST_SOURCE
        ]);

        $this->assertInstanceOf(EchoResponse::class, $response);
    }
}
