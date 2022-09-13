<?php

namespace Phpactor\Search\Tests\Unit\Model;

use Closure;
use Generator;
use GlobIterator;
use PHPUnit\Framework\TestCase;
use Phpactor\Search\Model\Matcher;
use Phpactor\Search\Model\Matches;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

class MatcherTest extends TestCase
{
    /**
     * @dataProvider provideMatch
     */
    public function testMatch(string $document, string $pattern, Closure $assertion): void
    {
        $matches = (new Matcher())->match(TextDocumentBuilder::create($document)->build(), $pattern);
        $assertion($matches);
    }

    /**
     * @return Generator<array{string|bool,<missing>,<missing>}>
     */
    public function provideMatch(): Generator
    {
        $cases = iterator_to_array($this->cases());
        /** @var SplFileInfo $splFileInfo */
        foreach ((new GlobIterator(__DIR__ . '/source/*.test')) as $splFileInfo) {
            $case = $splFileInfo->getBasename();
            if (!isset($cases[$case])) {
                $this->fail(sprintf('Could not find case for "%s"', $case));
            }
            $case = $cases[$case];
            yield [
                file_get_contents($splFileInfo->getPathName()),
                $case[0],
                $case[1]
            ];
        }
    }

    /**
     * @return Generator<string,array{string,Closure(Matches): void}>
     */
    public function cases(): Generator
    {
        yield 'test1.test' => [
            'class $foo',
            function (Matches $matches): void {
                dump($matches);
            }
        ];
    }
}
