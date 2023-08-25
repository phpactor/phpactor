<?php

namespace Phpactor\Diff\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\Diff\RangesForDiff;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use SebastianBergmann\Diff\Diff;
use SebastianBergmann\Diff\Parser;

class RangesForDiffTest extends TestCase
{
    private RangesForDiff $rangesForDiff;

    public function setUp(): void
    {
        $this->rangesForDiff = new RangesForDiff();
    }

    public function testNoChanges(): void
    {
        $emptyDiff = new Diff('', '');
        $ranges = $this->rangesForDiff->createRangesForDiff($emptyDiff);
        self::assertCount(0, $ranges);
    }

    /**
     * @param Range[] $expectedRanges
     * @dataProvider diffProvider
     */
    public function testCreatingRanges(string $diff, array $expectedRanges): void
    {
        $parser = new Parser();
        $diffObject = $parser->parse($diff)[0];

        $ranges = $this->rangesForDiff->createRangesForDiff($diffObject);
        self::assertEquals($expectedRanges, $ranges);
    }

    /**
     * @return iterable<string,array{
     *   0: string,
     *   1: array<Range>
     * }>
     */
    public function diffProvider(): iterable
    {
        yield 'multiple replacements' => [
          'diff' => <<<EOF
              --- php://stdin
              +++ php://stdin
              @@ -2,7 +2,7 @@
               namespace Test;
               \$foo = 'bar';
              -    \$test1 = true;
              -    \$test2 = true;
              -    \$test3 = true;
              +\$test1 = true;
              +\$test2 = true;
              +\$test3 = true;
               \$lao = "tzu";
              \ No newline at end of file
              @@ -5,4 +5,4 @@
                   \$test1 = true;
                   \$test2 = true;
                   \$test3 = true;
              -\$lao = "tzu";
              \ No newline at end of file
              +\$lao = "tzu";
              EOF,
          'ranges' => [
            new Range(
                new Position(3, 0),
                new Position(5, 4)
            ),
            new Range(
                new Position(7, 0),
                new Position(7, 13)
            ),
            ],
          ];

        yield 'addition' => [
          'diff' => <<<EOF
              --- php://stdin
              +++ php://stdin
              @@ -1,8 +1,9 @@
               <?php

               namespace Test;
              +
               \$foo = 'bar';
                   \$test1 = true;
                   \$test2 = true;
                   \$test3 = true;
               \$lao = "tzu";
              \ No newline at end of file
              EOF,
          'ranges' => [
            new Range(
                new Position(2, 0),
                new Position(2, 1)
            )
          ]
        ];

        yield 'deletion' => [
          'diff' => <<<EOF
              --- php://stdin
              +++ php://stdin
              @@ -1,9 +1,8 @@
               <?php

               namespace Test;
              -
               \$foo = 'bar';
                   \$test1 = true;
                   \$test2 = true;
                   \$test3 = true;
               \$lao = "tzu";
              \ No newline at end of file
              EOF,
          'ranges' => [
            new Range(
                new Position(3, 0),
                new Position(4, 0)
            )
          ]
        ];
    }
}
