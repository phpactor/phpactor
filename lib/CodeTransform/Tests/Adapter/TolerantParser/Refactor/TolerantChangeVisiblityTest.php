<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser\Refactor;

use Generator;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantChangeVisiblity;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\TolerantParser\TolerantTestCase;

class TolerantChangeVisiblityTest extends TolerantTestCase
{
    /**
     * @dataProvider provideChangeVisibility
     */
    public function testExtractExpression(string $test): void
    {
        [$source, $expected, $offsetStart] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $extractMethod = new TolerantChangeVisiblity();
        $transformed = $extractMethod->changeVisiblity(SourceCode::fromString($source), $offsetStart);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return Generator<string,array{string}>
     */
    public function provideChangeVisibility(): Generator
    {
        yield 'no op' => [ 'changeVisibility1.test' ];
        yield 'method: from public to protected' => [ 'changeVisibility2.test' ];
        yield 'property: from protected to private' => [ 'changeVisibility3.test' ];
        yield 'constant: from public to protected' => [ 'changeVisibility4.test' ];
        yield 'property: on keyword' => [ 'changeVisibility5.test' ];
    }
}
