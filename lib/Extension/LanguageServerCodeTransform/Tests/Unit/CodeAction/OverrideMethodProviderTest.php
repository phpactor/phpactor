<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\CodeAction;

use PHPUnit\Framework\Attributes\DataProvider;
use Amp\CancellationTokenSource;
use Closure;
use Generator;
use Phpactor\Extension\LanguageServerCodeTransform\CodeAction\OverrideMethodProvider;
use Phpactor\Extension\LanguageServerCodeTransform\Model\OverrideMethod\OverridableMethodFinder;
use Phpactor\Extension\LanguageServerCodeTransform\Tests\IntegrationTestCase;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\WorseReflection\Core\SourceCodeLocator\BruteForceSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;
use function Amp\Promise\wait;

class OverrideMethodProviderTest extends IntegrationTestCase
{
    #[DataProvider('provideOverrideMethod')]
    public function testOverrideMethod(string $manifest, Closure $assertion): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);


        [ $source, $offset ] = ExtractOffset::fromSource($this->workspace()->getContents('subject.php'));
        $provider = new OverrideMethodProvider(new OverridableMethodFinder(ReflectorBuilder::create()->addLocator(
            new BruteForceSourceLocator(ReflectorBuilder::create()->build(), $this->workspace()->path())
        )->build()));

        $cancel = (new CancellationTokenSource())->getToken();
        $item = new TextDocumentItem('/test.php', 'php', 1, $source);
        $codeActions = wait($provider->provideActionsFor(
            $item,
            ProtocolFactory::range(1, 1, 1, 1),
            $cancel,
        ));
        $assertion($codeActions, $codeActions);
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideOverrideMethod(): Generator
    {
        yield 'no parent class' => [
            <<<'EOT'
                // File: subject.php
                <?php class Foobar {}'
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(0, $codeActions);
            }

        ];
        yield 'protected' => [
            <<<'EOT'
                // File: foobar.php
                <?php class Foobar {protected function foo(): {}}'
                // File: subject.php
                <?php class Bar extends Foobar {}'
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(1, $codeActions);
            }
        ];
        yield 'private method' => [
            <<<'EOT'
                // File: foobar.php
                <?php class Foobar {private function foo(): {}}'
                // File: subject.php
                <?php class Bar extends Foobar {}'
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(0, $codeActions);
            }
        ];
        yield 'protected method but already overridden' => [
            <<<'EOT'
                // File: foobar.php
                <?php class Foobar {protected function foo(): {}}'
                // File: subject.php
                <?php class Bar extends Foobar {protected function foo(): {}}'
                EOT
            , function (array $codeActions, array $diagnostics): void {
                self::assertCount(0, $codeActions);
            }
        ];
    }
}
