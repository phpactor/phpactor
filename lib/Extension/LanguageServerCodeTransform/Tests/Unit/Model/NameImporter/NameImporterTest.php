<?php

declare(strict_types=1);

namespace LanguageServerCodeTransform\Unit\Model\NameImporter;

use Prophecy\PhpUnit\ProphecyTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Exception;
use Generator;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\AliasAlreadyUsedException;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameAlreadyImportedException;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameImport as ImportClassNameImport;
use Phpactor\CodeTransform\Domain\Refactor\ImportName as RefactorImportName;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporter;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\TextEdit as LspTextEdit;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;

class NameImporterTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_CONTENT = 'hello this is some text';
    const EXAMPLE_PATH = '/foobar.php';
    const EXAMPLE_OFFSET = 12;
    const EXAMPLE_PATH_URI = 'file:///foobar.php';

    /**
     * @var ObjectProphecy<RefactorImportName>
     */
    private ObjectProphecy $importNameProphecy;

    private Workspace $workspace;

    private TextDocumentItem $document;

    /**
     * @var array<LspTextEdit>
     */
    private array $lspTextEdits;

    private TextEdits $textEdits;

    private SourceCode $sourceCode;

    private ByteOffset $byteOffset;

    private NameImporter $subject;

    protected function setUp(): void
    {
        $this->document = new TextDocumentItem(self::EXAMPLE_PATH_URI, 'php', 1, self::EXAMPLE_CONTENT);
        $this->workspace = new Workspace();
        $this->workspace->open($this->document);

        $this->importNameProphecy = $this->prophesize(RefactorImportName::class);
        $this->byteOffset = ByteOffset::fromInt(self::EXAMPLE_OFFSET);

        $this->textEdits = TextEdits::one(
            TextEdit::create(23, 6, 'huhuhu')
        );

        $this->lspTextEdits = TextEditConverter::toLspTextEdits($this->textEdits, self::EXAMPLE_CONTENT);

        $this->sourceCode = SourceCode::fromStringAndPath(
            self::EXAMPLE_CONTENT,
            TextDocumentUri::fromString(self::EXAMPLE_PATH_URI)->path()
        );

        $this->subject = new NameImporter($this->importNameProphecy->reveal());
    }

    public static function provideTestImportData(): Generator
    {
        yield 'function' => [
            '\in_array',
            'function',
            ImportClassNameImport::forFunction('\in_array'),
        ];

        yield 'class' => [
            self::class,
            'class',
            ImportClassNameImport::forClass(self::class),
        ];
    }

    #[DataProvider('provideTestImportData')]
    public function testImport(
        string $fqn,
        string $importType,
        ImportClassNameImport $importClassNameImport
    ): void {
        $this->importNameProphecy->importName(
            $this->sourceCode,
            $this->byteOffset,
            $importClassNameImport
        )->willReturn($this->textEdits);

        $result = $this->subject->__invoke(
            $this->document,
            self::EXAMPLE_OFFSET,
            $importType,
            $fqn,
            true
        );

        self::assertTrue($result->isSuccess());
        self::assertEquals($importClassNameImport, $result->getNameImport());
        self::assertEquals($this->lspTextEdits, $result->getTextEdits());
        self::assertNull($result->getError());
    }

    public function testImportTransformException(): void
    {
        $error = new TransformException('error!!');

        $this->importNameProphecy->importName(
            $this->sourceCode,
            $this->byteOffset,
            ImportClassNameImport::forClass(Exception::class),
        )->willThrow($error);

        $result = $this->subject->__invoke(
            $this->document,
            self::EXAMPLE_OFFSET,
            'class',
            Exception::class,
            true
        );

        self::assertFalse($result->isSuccess());
        self::assertNull($result->getNameImport());
        self::assertNull($result->getTextEdits());
        self::assertSame($error, $result->getError());
    }

    public function testImportAliasAlreadyUsedException(): void
    {
        $import = ImportClassNameImport::forClass(Exception::class);
        $aliasAlreadyUsedException = new AliasAlreadyUsedException($import);

        $this->importNameProphecy->importName(
            $this->sourceCode,
            $this->byteOffset,
            $import,
        )->willThrow($aliasAlreadyUsedException);

        $aliasedNameImport = ImportClassNameImport::forClass(Exception::class, 'AliasedException');

        $this->importNameProphecy->importName(
            $this->sourceCode,
            $this->byteOffset,
            $aliasedNameImport,
        )->willReturn($this->textEdits);

        $result = $this->subject->__invoke(
            $this->document,
            self::EXAMPLE_OFFSET,
            'class',
            Exception::class,
            true
        );

        self::assertTrue($result->isSuccess());
        self::assertEquals($aliasedNameImport, $result->getNameImport());
        self::assertEquals($this->lspTextEdits, $result->getTextEdits());
        self::assertNull($result->getError());
    }

    public function testImportNameAlreadyImportedExceptionExisting(): void
    {
        $import = ImportClassNameImport::forClass(Exception::class);
        $nameAlreadyImportedException = new NameAlreadyImportedException(
            $import,
            'Exception',
            Exception::class
        );

        $this->importNameProphecy->importName(
            $this->sourceCode,
            $this->byteOffset,
            ImportClassNameImport::forClass(Exception::class),
        )->willThrow($nameAlreadyImportedException);

        $result = $this->subject->__invoke(
            $this->document,
            self::EXAMPLE_OFFSET,
            'class',
            Exception::class,
            true
        );

        self::assertTrue($result->isSuccess());
        self::assertEquals($import, $result->getNameImport());
        self::assertNull($result->getTextEdits());
        self::assertNull($result->getError());
    }

    public function testImportNameAlreadyImportedExceptionNotExisting(): void
    {
        $import = ImportClassNameImport::forClass(Exception::class);
        $nameAlreadyImportedException = new NameAlreadyImportedException(
            $import,
            'RuntimeException',
            RuntimeException::class
        );

        $this->importNameProphecy->importName(
            $this->sourceCode,
            $this->byteOffset,
            $import,
        )->willThrow($nameAlreadyImportedException);

        $aliasedNameImport = ImportClassNameImport::forClass(Exception::class, 'ExceptionException');

        $this->importNameProphecy->importName(
            $this->sourceCode,
            $this->byteOffset,
            $aliasedNameImport,
        )->willReturn($this->textEdits);

        $result = $this->subject->__invoke(
            $this->document,
            self::EXAMPLE_OFFSET,
            'class',
            Exception::class,
            true
        );

        self::assertTrue($result->isSuccess());
        self::assertEquals($aliasedNameImport, $result->getNameImport());
        self::assertEquals($this->lspTextEdits, $result->getTextEdits());
        self::assertNull($result->getError());
    }
}
