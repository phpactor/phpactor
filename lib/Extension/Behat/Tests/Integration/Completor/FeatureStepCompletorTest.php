<?php

namespace Phpactor\Extension\Behat\Tests\Integration\Completor;

use PHPUnit\Framework\Attributes\DataProvider;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Behat\BehatExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class FeatureStepCompletorTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @param array<int,array<string,mixed>> $expected
     */
    #[DataProvider('provideComplete')]
    public function testComplete(string $source, array $expected): void
    {
        [$source, $start, $end] = ExtractOffset::fromSource($source);
        $suggestions = iterator_to_array($this->completor()->complete(
            TextDocumentBuilder::create($source)->language('gherkin')->build(),
            ByteOffset::fromInt((int)$end)
        ));

        foreach ($expected as $index => $expectation) {
            $this->assertArraySubset($expectation, $suggestions[$index]->toArray());
        }
    }

    /**
     * @return Generator<string,array{string,array<int,array<string,mixed>>}>
     */
    public static function provideComplete(): Generator
    {
        yield 'all' => [
            <<<'EOT'
                Feature: Foobar

                    Scenario: Hello
                        Given <><>
                EOT
            , [
                [
                    'type' => 'snippet',
                    'name' => 'that I visit Berlin',
                    'short_description' => ExampleContext::class,
                    'range' => [ 51, 51],
                ],
                [
                    'type' => 'snippet',
                    'name' => 'I should run to Weisensee',
                    'short_description' => ExampleContext::class,
                    'range' => [ 51, 51],
                ],
            ]
        ];

        yield 'partial match' => [
            <<<'EOT'
                Feature: Foobar

                    Scenario: Hello
                        Given <>that I visit<>
                EOT
            , [
                [
                    'type' => 'snippet',
                    'name' => ' Berlin',
                    'label' => 'that I visit Berlin',
                    'short_description' => ExampleContext::class,
                    'range' => [ 51, 63],
                ],
            ]
        ];
    }

    private function completor(): Completor
    {
        $container = PhpactorContainer::fromExtensions([
            WorseReflectionExtension::class,
            FilePathResolverExtension::class,
            CompletionExtension::class,
            BehatExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            LoggingExtension::class,
        ], [
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../../../../../..',
            BehatExtension::PARAM_CONFIG_PATH => __DIR__ .'/behat.yml',
        ]);


        return $container
            ->expect(CompletionExtension::SERVICE_REGISTRY, TypedCompletorRegistry::class)
            ->completorForType('cucumber');
    }
}
