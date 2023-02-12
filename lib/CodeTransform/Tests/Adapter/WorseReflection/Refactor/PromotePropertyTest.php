<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Generator;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorsePromoteProperty;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class PromotePropertyTest extends WorseTestCase
{
    /**
     * @dataProvider providePromote
     */
    public function testPromoting(string $source, string $expected): void
    {
        [$source, $offsetStart, $offsetEnd] = ExtractOffset::fromSource($source);

        $promote = $this->createPromoteProperty($source, true, false);
        $transformed = $promote->promoteProperty(
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt($offsetStart)
        )->apply($source);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return Generator<string,array{string}>
     */
    public function providePromote(): Generator
    {
        yield 'Promoting inside a non constructor does nothing' => [
            <<<'EOT'
                <?php

                class DTO
                {
                    public function randomMethod(st<>ring $one) {}
                }
                EOT,
            <<<'EOT'
                <?php

                class DTO
                {
                    public function randomMethod(string $one) {}
                }
                EOT,
        ];

        yield 'Promoting inside a constructor' => [
            <<<'EOT'
                <?php

                class DTO
                {
                    public function __construct(st<>ring $one) {}
                }
                EOT,
            <<<'EOT'
                <?php

                class DTO
                {
                    public function __construct(private string $one) {}
                }
                EOT,
        ];
    }

    private function createPromoteProperty(string $source, bool $named = true, bool $hint = false): WorsePromoteProperty
    {
        return new WorsePromoteProperty(
            $this->reflectorForWorkspace($source),
            new Parser(),
            $this->updater(),
        );
    }
}
