<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ClassRecord;

class FileAbsolutePathBeginsWithTest extends TestCase
{
    public function testDoesNotBeginWith(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo')->setFilePath('/foobar');
        self::assertFalse(Criteria::fileAbsolutePathBeginsWith('/baz')->isSatisfiedBy($record));
    }

    public function testBeginsWith(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo')->setFilePath('/foobar/bazboo/baz.php');
        self::assertTrue(Criteria::fileAbsolutePathBeginsWith('/foobar')->isSatisfiedBy($record));
    }

    public function testBeginsWithTrailingSlash(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo')
            ->setFilePath('/foobar/bazboo/baz.php');

        self::assertTrue(
            Criteria::fileAbsolutePathBeginsWith('/foobar/')->isSatisfiedBy($record)
        );
    }
}
