<?php declare(strict_types=1);

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
        $emptyDiff = new Diff('a', 'a');
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
     *   'diff': string,
     *   'ranges': Range[]
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

        yield 'change first line' => [
          'diff' => <<<EOF
              --- /home/daniel/www/phpactor/phpactor/lib/Test.php
              +++ /home/daniel/www/phpactor/phpactor/lib/Test.php
              @@ -1,4 +1,4 @@
              -<?php
              +<?php declare(strict_types=1);

              namespace Phpactor;
              EOF,
          'ranges' => [
            new Range(
                new Position(0, 5),
                new Position(0, 5)
            )
          ]
        ];
    }
}
