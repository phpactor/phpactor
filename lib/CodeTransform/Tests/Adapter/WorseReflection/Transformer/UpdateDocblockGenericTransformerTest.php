<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use Generator;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeTransform\Adapter\DocblockParser\ParserDocblockUpdater;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\UpdateDocblockGenericTransformer;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\WorseReflection\Reflector;
use function Amp\Promise\wait;

class UpdateDocblockGenericTransformerTest extends WorseTestCase
{
    /**
     * @dataProvider provideTransform
     */
    public function testTransform(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $this->workspace()->put(
            'Example1.php',
            '<?php /** @template T */class Generic{ }'
        );
        $this->workspace()->put(
            'Example2.php',
            '<?php /** @template T of object */class NeedsObject{ }'
        );
        $reflector = $this->reflectorForWorkspace($example);
        $transformer = $this->createTransformer($reflector);
        $transformed = wait($transformer->transform($source))->apply($source);
        self::assertEquals($expected, $transformed);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideTransform(): Generator
    {
        yield 'add missing extends' => [
            <<<'EOT'
                <?php

                class Foobar extends Generic {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                /**
                 * @extends Generic<mixed>
                 */
                class Foobar extends Generic {
                }
                EOT
        ];
        yield 'updates missing extends' => [
            <<<'EOT'
                <?php

                /**
                 * @author Daniel
                 */
                class Foobar extends Generic {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                /**
                 * @author Daniel
                 * @extends Generic<mixed>
                 */
                class Foobar extends Generic {
                }
                EOT
        ];
        yield 'ignores valid extends' => [
            <<<'EOT'
                <?php

                /**
                 * @extends Generic<mixed>
                 */
                class Foobar extends Generic {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                /**
                 * @extends Generic<mixed>
                 */
                class Foobar extends Generic {
                }
                EOT
        ];
        yield 'adds extends' => [
            <<<'EOT'
                <?php

                /**
                 * @author Daniel
                 * @extends ThisIsWrong<mixed>
                 */
                class Foobar extends Generic {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                /**
                 * @author Daniel
                 * @extends Generic<mixed>
                 */
                class Foobar extends Generic {
                }
                EOT
        ];
        yield 'ignores compatible object' => [
            <<<'EOT'
                <?php

                /**
                 * @extends NeedsObject<Foobar>
                 */
                class Foobar extends NeedsObject {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                /**
                 * @extends NeedsObject<Foobar>
                 */
                class Foobar extends NeedsObject {
                }
                EOT
        ];
        yield 'does not fix incompatible object' => [
            <<<'EOT'
                <?php

                /**
                 * @extends NeedsObject<string>
                 */
                class Foobar extends NeedsObject {
                }
                EOT
            ,
            <<<'EOT'
                <?php

                /**
                 * @extends NeedsObject<string>
                 */
                class Foobar extends NeedsObject {
                }
                EOT
        ];
    }

    private function createTransformer(Reflector $reflector): UpdateDocblockGenericTransformer
    {
        return new UpdateDocblockGenericTransformer(
            $reflector,
            $this->updater(),
            $this->builderFactory($reflector),
            new ParserDocblockUpdater(DocblockParser::create(), new TextFormat())
        );
    }
}
