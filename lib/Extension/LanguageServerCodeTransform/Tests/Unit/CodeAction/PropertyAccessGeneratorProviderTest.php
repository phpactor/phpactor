<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use PHPUnit\Framework\Attributes\DataProvider;
use Amp\CancellationTokenSource;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\PropertyAccessGeneratorProvider;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\PhpUnit\ProphecyTrait;
use function Amp\Promise\wait;

class PropertyAccessGeneratorProviderTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_FILE = 'file:///somefile.php';

    protected function setUp(): void
    {
    }

    #[DataProvider('provideActionsTestData')]
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

    public static function provideActionsTestData(): Generator
    {
        yield 'provide actions'  => [
            '<?php class Foo { <>private $foo;<> }',
            [
                CodeAction::fromArray([
                    'title' =>  'Generate 1 accessor(s)',
                    'kind' => 'quickfix.generate_accessors',
                    'command' => new Command(
                        'Generate 1 accessor(s)',
                        'generate_accessors',
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

    private function createProvider(string $sourceCode): PropertyAccessGeneratorProvider
    {
        $reflector = ReflectorBuilder::create()->addSource($sourceCode)->build();
        return new PropertyAccessGeneratorProvider(
            'quickfix.generate_accessors',
            'generate_accessors',
            'accessor',
            $reflector,
        );
    }
}
