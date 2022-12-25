<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use Generator;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeTransform\Adapter\DocblockParser\ParserDocblockUpdater;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\UpdateDocblockParamsTransformer;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\UpdateDocblockTransformer;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\WorseReflection\Reflector;

class UpdateDocblockParamsTransformerTest extends WorseTestCase
{
    /**
     * @dataProvider provideTransform
     */
    public function testTransform(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $this->workspace()->put(
            'Example.php',
            '<?php namespace Namespaced; class NsTest { /** @return Baz[] */public function bazes(): array {}} class Baz{}'
        );
        $reflector = $this->reflectorForWorkspace($example);
        $transformer = $this->createTransformer($reflector);
        $transformed = $transformer->transform($source)->apply($source);
        self::assertEquals($expected, $transformed);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideTransform(): Generator
    {
        yield 'add missing param' => [
            <<<'EOT'
                <?php

                class Foobar {

                    /**
                     */
                    public function baz(array $param): array
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar {

                    /**
                     * @param array<int,mixed> $param
                     */
                    public function baz(array $param): array
                    {
                    }
                }
                EOT
        ];
    }

    private function createTransformer(Reflector $reflector): UpdateDocblockParamsTransformer
    {
        return new UpdateDocblockParamsTransformer(
            $reflector,
            $this->updater(),
            $this->builderFactory($reflector),
            new TextFormat(),
            new ParserDocblockUpdater(DocblockParser::create())
        );
    }
}
