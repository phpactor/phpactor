<?php

namespace Phpactor\Extension\WorseReflection\Tests\Example;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\ReflectorBuilder;
use function Amp\Promise\wait;

class DiagnosticsTest extends TestCase
{
    /**
     * @dataProvider provideDiagnostics
     */
    public function testDiagnostics(DiagnosticProvider $provider, DiagnosticExample $example): void
    {
        $reflector = ReflectorBuilder::create()->addSource($example->source)->addDiagnosticProvider($provider)->build();
        $diagnostics = wait($reflector->diagnostics(TextDocumentBuilder::fromPathAndString('file:///test', $example->source)));
        if ($example->valid) {
            self::assertCount(0, $diagnostics);
            return;
        }
        ($example->assertion)($diagnostics);
        $this->addToAssertionCount(1);
    }

    /**
     * @return Generator<array{DiagnosticProvider,DiagnosticExample}>
     */
    public function provideDiagnostics(): Generator
    {
        $container = PhpactorContainer::fromExtensions([
            WorseReflectionExtension::class
        ]);
        foreach ($container->getServiceIdsForTag(WorseReflectionExtension::TAG_DIAGNOSTIC_PROVIDER) as $serviceId => $_) {
            /** @var class-string<DiagnosticProvider> $serviceId */
            $provider = $container->get($serviceId);
            foreach ($provider->examples() as $example) {
                yield sprintf('%s %s', $serviceId, $example->title) => [
                    $provider,
                    $example
                ];
            }

        }
    }
}
