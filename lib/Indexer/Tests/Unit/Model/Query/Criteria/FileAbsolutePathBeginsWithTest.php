<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\TextDocument\TextDocumentUri;

class FileAbsolutePathBeginsWithTest extends TestCase
{
    public function testDoesNotBeginWith(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo')->setFilePath(TextDocumentUri::fromString('/foobar'));
        self::assertFalse(Criteria::fileAbsolutePathBeginsWith('/baz')->isSatisfiedBy($record));
    }

    public function testBeginsWith(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo')->setFilePath(TextDocumentUri::fromString('/foobar/bazboo/baz.php'));
        self::assertTrue(Criteria::fileAbsolutePathBeginsWith('/foobar')->isSatisfiedBy($record));
    }

    public function testBeginsWithTrailingSlash(): void
    {
        $record = ClassRecord::fromName('Foobar\\Barfoo')
            ->setFilePath(TextDocumentUri::fromString('/foobar/bazboo/baz.php'));

        self::assertTrue(
            Criteria::fileAbsolutePathBeginsWith('/foobar/')->isSatisfiedBy($record)
        );
    }
}
