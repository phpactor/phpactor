<?php

namespace Phpactor\Indexer\Tests\Adapter\Worse;

use Generator;
use Phpactor\TextDocument\FilesystemTextDocumentLocator;
use Phpactor\Indexer\Adapter\Worse\WorseRecordReferenceEnhancer;
use Phpactor\Indexer\Model\RecordReference;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\WorseReflection\ReflectorBuilder;
use Psr\Log\NullLogger;

class WorseRecordReferenceEnhancerTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideEnhance
     */
    public function testEnhance(string $source, string $expectedType): void
    {
        [$source,$offset] = ExtractOffset::fromSource($source);

        $this->workspace()->reset();
        $this->workspace()->put('test.php', $source);
        $reflector = ReflectorBuilder::create()->enableContextualSourceLocation()->build();
        $enhancer = new WorseRecordReferenceEnhancer(
            $reflector,
            new NullLogger(),
            new FilesystemTextDocumentLocator(),
        );
        $fileRecord = FileRecord::fromPath($this->workspace()->path('test.php'));
        $reference = new RecordReference(MemberRecord::RECORD_TYPE, 'foobar', (int)$offset, (int)$offset);
        $reference = $enhancer->enhance($fileRecord, $reference);
        self::assertEquals($expectedType, $reference->contaninerType());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideEnhance(): Generator
    {
        yield [
            <<<'EOT'
                <?php

                namespace Foo;

                class Foobar
                {
                    public function bar(): string
                    {
                    }
                }

                $foobar = new Foobar();
                $foobar->b<>ar();
                EOT
        ,
            'Foo\Foobar',
        ];
    }
}
