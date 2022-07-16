<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use Amp\CancellationTokenSource;
use Closure;
use Generator;
use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FilePathCandidates;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnresolvableNameProvider;
use Prophecy\PhpUnit\ProphecyTrait;
use function Amp\Promise\wait;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\CreateUnresolvableClassProvider;
use Phpactor\Extension\LanguageServerCodeTransform\Tests\IntegrationTestCase;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\WorseReflection\ReflectorBuilder;

class CreateUnresolvableClassProviderTest extends IntegrationTestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideCodeAction
     */
    public function testReturnsCodeActions(string $source, Closure $assertion): void
    {
        [$source, $start, $end] = ExtractOffset::fromSource($source);

        $classToFile = $this->prophesize(ClassToFile::class);
        $classToFile->classToFileCandidates(ClassName::fromString('Foo'))->willReturn(FilePathCandidates::fromFilePaths([FilePath::fromString('/foo')]));
        $reflector = ReflectorBuilder::create()->addDiagnosticProvider(new UnresolvableNameProvider(false))->build();
        $provider = new CreateUnresolvableClassProvider($reflector, $classToFile->reveal());
        $actions = wait($provider->provideActionsFor(
            ProtocolFactory::textDocumentItem('file://foo', $source),
            RangeConverter::toLspRange(ByteOffsetRange::fromInts($start, $end), $source),
            (new CancellationTokenSource)->getToken(),
        ));
        $assertion(...$actions);
    }

    /**
     * @return Generator<array{string,Closure(CodeAction[]): void}>
     */
    public function provideCodeAction(): Generator
    {
        yield 'empty file' => [
            '<<>?php <>',
            function (CodeAction ...$actions): void {
                self::assertCount(0, $actions);
            }
        ];
        yield [
            '<?php new Fo<>o<>();',
            function (CodeAction ...$actions): void {
                self::assertCount(1, $actions);
            }
        ];
    }
}
