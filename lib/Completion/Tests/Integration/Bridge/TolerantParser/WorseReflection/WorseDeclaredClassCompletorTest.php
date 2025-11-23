<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseDeclaredClassCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseDeclaredClassCompletorTest extends TolerantCompletorTestCase
{
    #[DataProvider('provideComplete')]
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    /**
     * @return Generator<string,array{string,array<int,array<string,string>>}>
     */
    public static function provideComplete(): Generator
    {
        yield 'array object' => [
            <<<'EOT'
                <?php

                $class = new RangeException<>
                EOT
        ,
            [
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'name' => 'RangeException',
                ]
            ]
        ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()
            ->addLocator(new StubSourceLocator(
                ReflectorBuilder::create()->build(),
                __DIR__ . '/../../../../../../../vendor/jetbrains/phpstorm-stubs',
                __DIR__ . '/../../../../../cache'
            ))
            ->addSource($source)
            ->build();

        return new WorseDeclaredClassCompletor($reflector, $this->formatter());
    }
}
