<?php

namespace Phpactor\Indexer\Tests\Adapter\Tolerant\Indexer;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Indexer\Adapter\Tolerant\Indexer\MemberIndexer;
use Phpactor\Indexer\Model\LocationConfidence;
use Phpactor\Indexer\Model\MemberReference;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\Indexer\Tests\Adapter\Tolerant\TolerantIndexerTestCase;

class MemberIndexerTest extends TolerantIndexerTestCase
{
    /**
     * @param array{int,int,int} $expectedCounts
     */
    #[DataProvider('provideStaticAccess')]
    #[DataProvider('provideInstanceAccess')]
    public function testMembers(string $manifest, MemberReference $memberReference, array $expectedCounts): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);

        $agent = $this->runIndexer(new MemberIndexer(), 'src');

        $memberRecord = $agent->index()->get(MemberRecord::fromMemberReference($memberReference));
        assert($memberRecord instanceof MemberRecord);

        $counts = [
            LocationConfidence::CONFIDENCE_NOT => 0,
            LocationConfidence::CONFIDENCE_MAYBE => 0,
            LocationConfidence::CONFIDENCE_SURELY => 0,
        ];

        foreach ($agent->query()->member()->referencesTo(
            $memberReference->type(),
            $memberReference->memberName(),
            $memberReference->containerType()
        ) as $locationCondidence) {
            $counts[$locationCondidence->__toString()]++;
        }

        self::assertEquals(array_combine(array_keys($counts), $expectedCounts), $counts);
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideStaticAccess(): Generator
    {
        yield 'single ref' => [
            "// File: src/file1.php\n<?php Foobar::static()",
            MemberReference::create(MemberRecord::TYPE_METHOD, 'Foobar', 'static'),
            [0, 0, 1]
        ];

        yield '> 1 same name method with different container type and specified search type' => [
            "// File: src/file1.php\n<?php Foobar::static(); Barfoo::static();",
            MemberReference::create(MemberRecord::TYPE_METHOD, 'Foobar', 'static'),
            [ 1, 0, 1 ],
        ];

        yield '> 1 same name method with different container type and no specified search type' => [
            "// File: src/file1.php\n<?php Foobar::static(); Barfoo::static();",
            MemberReference::create(MemberRecord::TYPE_METHOD, null, 'static'),
            [ 0, 0, 2 ],
        ];

        yield 'multiple ref' => [
            "// File: src/file1.php\n<?php Foobar::static(); Foobar::static();",
            MemberReference::create(MemberRecord::TYPE_METHOD, 'Foobar', 'static'),
            [ 0, 0, 2 ],
        ];

        yield MemberRecord::TYPE_CONSTANT => [
            "// File: src/file1.php\n<?php Foobar::FOOBAR;",
            MemberReference::create(MemberRecord::TYPE_CONSTANT, 'Foobar', 'FOOBAR'),
            [ 0, 0, 1 ],
        ];

        yield 'constant in call' => [
            "// File: src/file1.php\n<?php get(Foobar::FOOBAR);",
            MemberReference::create(MemberRecord::TYPE_CONSTANT, 'Foobar', 'FOOBAR'),
            [ 0, 0, 1 ]
        ];

        yield MemberRecord::TYPE_PROPERTY => [
            "// File: src/file1.php\n<?php Foobar::\$foobar;",
            MemberReference::create(MemberRecord::TYPE_PROPERTY, 'Foobar', 'foobar'),
            [ 0, 0, 1 ]
        ];

        yield 'namespaced static access' => [
            "// File: src/file1.php\n<?php use Barfoo\\Foobar; Foobar::hello();",
            MemberReference::create(MemberRecord::TYPE_METHOD, 'Barfoo\\Foobar', 'hello'),
            [ 0, 0, 1 ]
        ];

        yield 'self' => [
            "// File: src/file1.php\n<?php class Foobar { function bar() {} function foo() { self::bar(); } }",
            MemberReference::create(MemberRecord::TYPE_METHOD, 'Foobar', 'bar'),
            [ 0, 0, 1 ]
        ];

        yield 'self (property)' => [
            "// File: src/file1.php\n<?php class Foobar { static \$barProp; function foo() { self::\$barProp = 5; \$var1 = self::\$barProp; } }",
            MemberReference::create(MemberRecord::TYPE_PROPERTY, 'Foobar', 'barProp'),
            [ 0, 0, 2 ]
        ];

        yield 'parent' => [
            "// File: src/file1.php\n<?php class Foobar { function bar() {}} class Barfoo extends Foobar { function foo() { parent::bar(); } }",
            MemberReference::create(MemberRecord::TYPE_METHOD, 'Foobar', 'bar'),
            [ 0, 0, 1 ]
        ];

        yield 'property with invalid access' => [
            "// File: src/file1.php\n<?php \$json = json_encode(['some#hash' => 'value']);\$object = json_decode(\$json);echo \$object->{'some#hash'};",
            MemberReference::create(MemberRecord::TYPE_METHOD, 'Foobar', 'bar'),
            [ 0, 0, 0 ]
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideInstanceAccess(): Generator
    {
        yield 'method call with wrong container type' => [
            "// File: src/file1.php\n<?php class Foobar {}; \$foobar = new Foobar(); \$foobar->hello();",
            MemberReference::create(MemberRecord::TYPE_METHOD, 'Barfoo', 'hello'),
            [ 1, 0, 0 ],
        ];

        yield 'method call' => [
            "// File: src/file1.php\n<?php \$foobar->hello();",
            MemberReference::create(MemberRecord::TYPE_METHOD, 'Foobar', 'hello'),
            [ 0, 1, 0 ],
        ];

        yield 'property access' => [
            "// File: src/file1.php\n<?php \$foobar->hello;",
            MemberReference::create(MemberRecord::TYPE_PROPERTY, 'Foobar', 'hello'),
            [ 0, 1, 0 ],
        ];

        yield 'resolvable property instance container type' => [
            "// File: src/file1.php\n<?php class Foobar {}; \$foobar = new Foobar(); \$foobar->hello;",
            MemberReference::create(MemberRecord::TYPE_PROPERTY, 'Foobar', 'hello'),
            [ 0, 0, 1 ],
        ];
    }
}
