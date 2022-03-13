<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod;
use Phpactor\Completion\Core\Util\OffsetHelper;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\GenerateAccessorsProvider;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\GenerateMethodProvider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateAccessorsCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateMethodCommand;
use Phpactor\Extension\LanguageServerRename\Tests\Util\OffsetExtractor;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use function Amp\Promise\wait;

class GenerateAccessorProviderTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_FILE = 'file:///somefile.php';

    protected function setUp(): void
    {
    }

    /**
     * @dataProvider provideActionsTestData
     */
    public function testProvideActions(string $sourceCode, array $expectedActions): void
    {
        [$source, $start, $end] = ExtractOffset::fromSource($sourceCode);
        $provider = $this->createProvider($source);

        self::assertEquals(
            $expectedActions,
            wait($provider->provideActionsFor(
                new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, $source),
                RangeConverter::toLspRange(ByteOffsetRange::fromInts((int)$start, (int)$end), $source)
            ))
        );
    }

    public function provideActionsTestData(): Generator
    {
        yield 'provide actions'  => [
            '<?php class Foo { <>private $foo;<> }',
            [
                CodeAction::fromArray([
                    'title' =>  'Generate accessors',
                    'kind' => GenerateAccessorsProvider::KIND,
                    'command' => new Command(
                        'Generate 1 accessor(s)',
                        GenerateAccessorsCommand::NAME,
                        [
                            self::EXAMPLE_FILE,
                            18,
                            ['foo'],
                        ]
                    )
                ])
            ]
        ];
    }

    private function createProvider(string $sourceCode): GenerateAccessorsProvider
    {
        $reflector = ReflectorBuilder::create()->addSource($sourceCode)->build();
        return new GenerateAccessorsProvider($reflector);
    }
}
