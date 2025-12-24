<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use Microsoft\PhpParser\Node\Expression\Variable;
use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Phpactor\WorseReflection\Core\Cache\StaticCache;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\VariableResolver;
use Phpactor\WorseReflection\Core\Inference\Walker\PassThroughWalker;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Closure;
use Psr\Log\NullLogger;

class FrameResolverTest extends IntegrationTestCase
{
    #[DataProvider('provideForMethod')]
    public function testForMethod(string $source, array $classAndMethod, Closure $assertion): void
    {
        [$className, $methodName] = $classAndMethod;
        $reflector = $this->createReflector($source);
        $method = $reflector->reflectClassLike(ClassName::fromString($className))->methods()->get($methodName);
        $frame = $method->frame();

        $assertion($frame, $this->logger());
    }

    /**
     * @return Generator<string,array{string,array<int,string>,Closure(Frame,LoggerInterface): void}>
     */
    public static function provideForMethod(): Generator
    {
        yield 'Tolerates missing assert arguments' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        assert();
                    }
                }
                EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame, $logger): void {
            self::assertEquals(0, $frame->problems()->count(), $frame->problems()->__toString());
        }];

        yield 'Tolerates missing tokens' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        $reflection = )>classReflector->reflect(TestCase::class);
                    }
                }
                EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame, $logger): void {
            self::assertEquals(0, $frame->problems()->count());
        }];
    }

    #[DataProvider('provideCache')]
    public function testCache(string $source, int $expectedCacheMisses): void
    {
        preg_match_all('{(<[0-9]+>)}', $source, $matches, PREG_OFFSET_CAPTURE);
        $edits = [];
        foreach ($matches[0] as [$placeholder, $offset]) {
            $edits[] = TextEdit::create(ByteOffset::fromInt((int)$offset), strlen($placeholder), '');
            $index = (int)trim($placeholder, '<>');
            $offsets[$index] = $offset;
        }
        ksort($offsets);

        $source = TextEdits::fromTextEdits($edits)->apply($source);
        $reflector = $this->createReflector($source);
        $docblockFactory = $this->createMock(DocBlockFactory::class);
        $cache = new StaticCache();

        $ast = (new \Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider())->get($source, 'file:///test.php');

        foreach ($offsets as $offset) {
            $nodeResolver = new NodeContextResolver(
                $reflector,
                $docblockFactory,
                new NullLogger(),
                $cache,
                []
            );
            $frameResolver = new FrameResolver(
                $nodeResolver,
                [
                    new PassThroughWalker(),
                ],
                [
                    Variable::class => new VariableResolver(),
                ],
                new CacheForDocument(fn () => $cache),
            );
            $node = $ast->getDescendantNodeAtPosition($offset);
            $frame = $frameResolver->build($node);
        }

        self::assertEquals($expectedCacheMisses, $nodeResolver->cacheMisses);
    }

    public static function provideCache(): Generator
    {
        yield 'cold cache' => [
            <<<'PHP'
                <?php
                $v1;
                $<1>v2;
                PHP,
            6
        ];

        yield 'uses cache if before last' => [
            <<<'PHP'
                <?php
                $<2>v1;
                $<1>v2;
                PHP,
            0
        ];

        yield 'rebuilds cache if ahead of first' => [
            <<<'PHP'
                <?php
                $<1>v1;
                $<2>v2;
                PHP,
            6
        ];
    }
}
