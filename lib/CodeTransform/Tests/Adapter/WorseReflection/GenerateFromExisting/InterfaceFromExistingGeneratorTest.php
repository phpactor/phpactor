<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\GenerateFromExisting;

use Generator;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Adapter\WorseReflection\GenerateFromExisting\InterfaceFromExistingGenerator;

class InterfaceFromExistingGeneratorTest extends WorseTestCase
{
    /**
     * @testdox Generate interface
     * @dataProvider provideGenerateInterface
     */
    public function testGenerateInterface(string $className, string $targetName, string $source, string $expected): void
    {
        $reflector = $this->reflectorForWorkspace($source);
        $generator = new InterfaceFromExistingGenerator($reflector, $this->renderer());
        $source = $generator->generateFromExisting(ClassName::fromString($className), ClassName::fromString($targetName));
        $this->assertEquals($expected, (string) $source);
    }

    /**
     * @return Generator<string,array{string,string,string,string}>
     */
    public function provideGenerateInterface(): Generator
    {
        yield 'Generates interface' => [
            'Music\Beat',
            'Music\BeatInterface',
            <<<'EOT'
                <?php

                namespace Music;

                use Sound\Snare;

                class Beat
                {
                    private $foobar;

                    public function __construct(string $foobar)
                    {
                        $this->foobar = $foobar;
                    }

                    /**
                     * This is some documentation.
                     */
                    public function play(Snare $snare = null, int $bar = "boo")
                    {
                        $snare->hit();
                    }

                    public function empty()
                    {
                    }

                    private function something()
                    {
                    }

                    protected function somethingElse()
                    {
                    }
                }
                EOT
            , <<<'EOT'
                <?php

                namespace Music;

                use Sound\Snare;

                interface BeatInterface
                {
                    /**
                     * This is some documentation.
                     */
                    public function play(Snare $snare = null, int $bar = 'boo');

                    public function empty();
                }
                EOT

        ];
        yield 'Generates interface with return types' => [
            'Music\Beat',
            'Music\BeatInterface',
            <<<'EOT'
                <?php

                namespace Music;

                use Sound\Snare;

                class Beat
                {
                    public function play(Snare $snare = null, int $bar = "boo"): Music
                    {
                    }
                }
                EOT
            , <<<'EOT'
                <?php

                namespace Music;

                use Music\Music;
                use Sound\Snare;

                interface BeatInterface
                {
                    public function play(Snare $snare = null, int $bar = 'boo'): Music;
                }
                EOT

        ];
        yield 'Does not import scalar types' => [
            'Music\Beat',
            'Music\BeatInterface',
            <<<'EOT'
                <?php

                namespace Music;

                class Beat
                {
                    public function play(): string
                    {
                    }
                }
                EOT
            , <<<'EOT'
                <?php

                namespace Music;

                interface BeatInterface
                {
                    public function play(): string;
                }
                EOT

        ];
    }
}
