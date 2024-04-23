<?php

namespace Phpactor\ClassMover\Tests\Adapter\WorseTolerant;

use Generator;
use Phpactor\ClassMover\Domain\SourceCode;
use Phpactor\ClassMover\Adapter\WorseTolerant\WorseTolerantMemberReplacer;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;

class WorseTolerantMemberReplacerTest extends WorseTolerantTestCase
{
    /**
     * @testdox It replaces all member references
     * @dataProvider provideTestReplace
     */
    public function testReplace(string $classFqn, string $memberName, string $newMemberName, string $source, string $expectedSource): void
    {
        $finder = $this->createFinder($source);
        $source = SourceCode::fromString($source);

        $references = $finder->findMembers($source, ClassMemberQuery::create()->withClass($classFqn)->withMember($memberName));

        $replacer = new WorseTolerantMemberReplacer();
        $source = $replacer->replaceMembers($source, $references, $newMemberName);
        $this->assertStringContainsString($expectedSource, $source->__toString());
    }

    /** @return Generator<array<string>> */
    public function provideTestReplace(): Generator
    {
        yield 'It returns unmodified if no references' => [
            'Foobar', 'zzzzz', 'barfoo',
            <<<'EOT'
                <?php
                $foobar = new Foobar();
                $foobar->foobar();
                EOT
            , <<<'EOT'
                <?php
                $foobar = new Foobar();
                $foobar->foobar();
                EOT
        ];
        yield 'It replaces references' => [
            'Foobar', 'foobar', 'barfoo',
            <<<'EOT'
                <?php
                class Foobar { function foobar() {} }

                $foobar = new Foobar();
                $foobar->foobar();
                EOT
            , <<<'EOT'
                $foobar->barfoo();
                EOT
        ];
        yield 'It replaces member declarations' => [
            'Foobar', 'foobar', 'barfoo',
            <<<'EOT'
                <?php
                class Foobar { function foobar() {} }

                $foobar = new Foobar();
                $foobar->foobar();
                EOT
            , <<<'EOT'
                class Foobar { function barfoo() {} }
                EOT
        ];
        yield 'It replaces property declarations' => [
            'Foobar', 'foobar', 'barfoo',
            <<<'EOT'
                <?php
                class Foobar { protected $foobar; {} }

                $foobar = new Foobar();
                $foobar->foobar;
                EOT
            , <<<'EOT'
                class Foobar { protected $barfoo; {} }
                EOT
        ];
        yield 'It replaces static property declarations' => [
            'Foobar', 'foobar', 'barfoo',
            <<<'EOT'
                <?php
                class Foobar { public static $foobar; {} }

                Foobar::$foobar;
                EOT
            , <<<'EOT'
                class Foobar { public static $barfoo; {} }

                Foobar::$barfoo;
                EOT
        ];
        yield 'It replaces constants' => [
            'Foobar', 'BARFOO', 'FOO',
            <<<'EOT'
                <?php
                class Foobar { const BARFOO = 1; {} }

                Foobar::BARFOO;
                EOT
            , <<<'EOT'
                <?php
                class Foobar { const FOO = 1; {} }

                Foobar::FOO;
                EOT
        ];
    }
}
