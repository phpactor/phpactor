<?php

namespace Phpactor\Extension\PhpSpec\Tests\Integration\Provider;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\PhpSpec\PhpSpecExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Completion\Core\Completor;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

final class ObjectBehaviorMemberProviderTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @dataProvider provideComplete
     * @param array<int,array<string,mixed>> $expected
     */
    public function testComplete(string $source, array $expected): void
    {
        [$source, $start] = ExtractOffset::fromSource($source);
        $suggestions = iterator_to_array($this->completor()->complete(
            TextDocumentBuilder::create($source)->language('php')->build(),
            ByteOffset::fromInt((int)$start)
        ));
        dump($suggestions);

        foreach ($expected as $index => $expectation) {
            $this->assertArraySubset($expectation, $suggestions[$index]->toArray());
        }
    }

    /**
     * @return Generator<string,array{string,array<int,array<string,mixed>>}>
     */
    public function provideComplete(): Generator
    {
        yield '$this' => [
            $this->createSource(
                <<<'EOT'
                        namespace spec\App\Model;

                        use App\Model\Employee;
                        use PhpSpec\ObjectBehavior;

                        final class EmployeeSpec extends ObjectBehavior
                        {
                            public function it_tests(): void
                            {
                                $this-><>
                            }
                        }
                    EOT
            )
            , [
                ['type' => 'method', 'name' => 'shouldBe'],
                ['type' => 'method', 'name' => 'it_tests'],
                ['type' => 'method', 'name' => 'company'],
            ]
        ];

        // TODO: Use a Walker to solve this ?
        // yield '$this->company()' => [
        //     $this->createSource(
        //         <<<'EOT'
        //             namespace spec\App\Model;

        //             use App\Model\Employee;
        //             use PhpSpec\ObjectBehavior;

        //             final class EmployeeSpec extends ObjectBehavior
        //             {
        //                 public function it_tests(): void
        //                 {
        //                     $this->company()-><>
        //                 }
        //             }
        //             EOT
        //     )
        //     , [
        //         ['type' => 'method', 'name' => 'shouldBe'],
        //         ['type' => 'method', 'name' => 'name'],
        //     ]
        // ];
    }

    private function completor(): Completor
    {
        $container = PhpactorContainer::fromExtensions([
            WorseReflectionExtension::class,
            FilePathResolverExtension::class,
            CompletionExtension::class,
            CompletionWorseExtension::class,
            ReferenceFinderExtension::class,
            SourceCodeFilesystemExtension::class,
            PhpSpecExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            LoggingExtension::class,
        ], [
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../../../../../..',
            PhpSpecExtension::PARAM_ENABLED => true,
        ]);
        
        return $container->get(CompletionExtension::SERVICE_REGISTRY)->completorForType('php');
    }

    private function createSource(string $source): string
    {
        return <<<"EOT"
                <?php

                namespace PhpSpec;

                final class ObjectBehavior
                {
                    public function shouldBe()
                    {
                    }
                }

                namespace App\Model;

                final class Company
                {
                    public function name(): string
                    {
                        return 'Company name';
                    }
                }

                final class Employee
                {
                    public function company(): Company
                    {
                        return new Company();
                    }
                }

                $source
            EOT;
    }
}
