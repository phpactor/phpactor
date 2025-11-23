<?php

namespace Phpactor\Indexer\Tests\Unit\Adapter\ReferenceFinder\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Indexer\Adapter\ReferenceFinder\Util\ContainerTypeResolver;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Phpactor\TestUtils\ExtractOffset;

class ContainerTypeResolverTest extends IntegrationTestCase
{
    #[DataProvider('provideResolve')]
    public function testResolve(
        array $manifest,
        string $memberType,
        string $memberName,
        ?string $containerType,
        ?string $expectedType
    ): void {
        $this->workspace()->reset();
        $this->workspace()->loadManifest(implode("\n", $manifest));
        $source = $this->workspace()->getContents('test.php');
        [$source, $offset] = ExtractOffset::fromSource($source);

        $type = (new ContainerTypeResolver($this->createReflector()))->resolveDeclaringContainerType(
            /** @phpstan-ignore-next-line */
            $memberType,
            $memberName,
            $containerType
        );

        self::assertEquals($expectedType, $type);
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideResolve(): Generator
    {
        yield 'no container type' => [
            ["// File: test.php\n"],
            'method',
            'foobar',
            null,
            null
        ];

        yield 'declaring container type' => [
            ["// File: test.php\n<?php class Foobar { public function barfoo() {}}"],
            'method',
            'barfoo',
            'Foobar',
            'Foobar',
        ];

        yield 'parent container type' => [
            [
                "// File: test.php\n<?php class Foobar { public function barfoo() {}}",
                "// File: one.php\n<?php class Barfoo extends Foobar {}",
            ],
            'method',
            'barfoo',
            'Barfoo',
            'Foobar',
        ];

        yield 'parent container type with overridden type' => [
            [
                "// File: test.php\n<?php class Foobar { public function barfoo() {}}",
                "// File: one.php\n<?php class Barfoo extends Foobar { public function barfoo() {}}",
            ],
            'method',
            'barfoo',
            'Barfoo',
            'Foobar',
        ];

        yield 'parent or parent container type with overridden type' => [
            [
                "// File: test.php\n<?php class Foobar { public function barfoo() {}}",
                "// File: one.php\n<?php class Barfoo extends Foobar { public function barfoo() {}}",
                "// File: two.php\n<?php class Carfoo extends Barfoo { public function barfoo() {}}",
            ],
            'method',
            'barfoo',
            'Carfoo',
            'Foobar',
        ];

        yield 'interface method' => [
            [
                "// File: test.php\n<?php interface Foobar { public function barfoo() {}}",
                "// File: one.php\n<?php class Barfoo implements Foobar { public function barfoo() {}}",
            ],
            'method',
            'barfoo',
            'Barfoo',
            'Foobar',
        ];
    }
}
