<?php

namespace Phpactor\Diff\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\Diff\DiffToTextEditsConverter;

class DiffToTextEditsConverterTest extends TestCase
{
    public function testToTextEdits(): void
    {
        $diff = <<<EOF
            --- lao 2002-02-21 23:30:39.942229878 -0800
            +++ tzu 2002-02-21 23:30:50.442260588 -0800
            @@ -1,7 +1,6 @@
            -The Way that can be told of is not the eternal Way;
            -The name that can be named is not the eternal name.
             The Nameless is the origin of Heaven and Earth;
            -The Named is the mother of all things.
            +The named is the mother of all things.
            +
             Therefore let there always be non-being,
               so we may see their subtlety,
             And let there always be being,
            @@ -9,3 +8,6 @@
             The two are the same,
             But after they are produced,
               they have different names.
            +They both may be called deep and profound.
            +Deeper and more profound,
            +The door of all subtleties!
            EOF;

        $converter = new DiffToTextEditsConverter();

        $edits = $converter->toTextEdits($diff);

        self::assertCount(3, $edits, '3 changes expected: removal of 2 first lines, adding extra line in middle, adding extra content on the end');

        // first - removal of 2 first lines
        self::assertEquals($edits[0]->range->start->line, 0);
        self::assertEquals($edits[0]->range->end->line, 2);
        self::assertEquals($edits[0]->newText, '');

        // second - removes line, and replaces it with new text (with extra new line)
        self::assertEquals($edits[1]->range->start->line, 3);
        self::assertEquals($edits[1]->range->end->line, 4);
        self::assertEquals($edits[1]->newText, "The named is the mother of all things.\n\n");

        // third - adds lines on the end
        self::assertEquals($edits[2]->range->start->line, 11);
        self::assertEquals($edits[2]->range->end->line, 11);
        self::assertEquals(
            $edits[2]->newText,
            <<<EOF
                They both may be called deep and profound.
                Deeper and more profound,
                The door of all subtleties!

                EOF
        );
    }
}
