<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Amp\CancellationTokenSource;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\GenerateMutatorsProvider;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateMutatorsCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\PhpUnit\ProphecyTrait;
use function Amp\Promise\wait;

class GenerateMutatorProviderTest extends TestCase
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

        $cancel = (new CancellationTokenSource())->getToken();
        self::assertEquals(
            $expectedActions,
            wait($provider->provideActionsFor(
                new TextDocumentItem(self::EXAMPLE_FILE, 'php', 1, $source),
                RangeConverter::toLspRange(ByteOffsetRange::fromInts((int)$start, (int)$end), $source),
                $cancel
            ))
        );
    }

    public function provideActionsTestData(): Generator
    {
        yield 'provide actions'  => [
            '<?php class Foo { <>private $foo;<> }',
            [
                CodeAction::fromArray([
                    'title' =>  'Generate 1 mutator(s)',
                    'kind' => GenerateMutatorsProvider::KIND,
                    'command' => new Command(
                        'Generate 1 mutator(s)',
                        GenerateMutatorsCommand::NAME,
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

    private function createProvider(string $sourceCode): GenerateMutatorsProvider
    {
        $reflector = ReflectorBuilder::create()->addSource($sourceCode)->build();
        return new GenerateMutatorsProvider($reflector);
    }
}
