<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\WorseReflection\Core\Util\OriginalMethodResolver;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class OriginalMethodResolverTest extends IntegrationTestCase
{
    #[DataProvider('provideResolve')]
    public function testResolve(
        array $manifest,
        string $memberType,
        string $memberName,
        string $containerType,
        ?string $expectedType
    ): void {
        $this->workspace()->reset();
        $this->workspace()->loadManifest(implode("\n", $manifest));
        $source = $this->workspace()->getContents('test.php');
        [$source, $offset] = ExtractOffset::fromSource($source);

        $reflector = $this->createWorkspaceReflector($source);

        $member = $reflector->reflectClassLike($containerType)->members()->byMemberType($memberType)->get($memberName);

        $member = (new OriginalMethodResolver($reflector))->resolveOriginalMember($member);

        self::assertEquals($expectedType, $member->declaringClass()->name()->__toString());
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideResolve(): Generator
    {
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

        yield 'interface extending interface' => [
            [
                "// File: test.php\n<?php interface Foobar { public function barfoo() {}}",
                "// File: one.php\n<?php interface Barfoo extends Foobar { public function barfoo() {}}",
            ],
            'method',
            'barfoo',
            'Barfoo',
            'Foobar',
        ];
    }
}
