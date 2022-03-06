<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\MemberRecord;

class ShortNameContainsTest extends TestCase
{
    public function testContains(): void
    {
        self::assertTrue(
            Criteria::shortNameContains('foobar')->isSatisfiedBy(
                MemberRecord::fromIdentifier('method#foobar')
            )
        );
        self::assertTrue(
            Criteria::shortNameContains('ooba')->isSatisfiedBy(
                MemberRecord::fromIdentifier('method#foobar')
            )
        );
        self::assertTrue(
            Criteria::shortNameContains('OoBa')->isSatisfiedBy(
                MemberRecord::fromIdentifier('method#foobar')
            ),
            'Case insensitive'
        );
        self::assertFalse(
            Criteria::shortNameContains('foobar')->isSatisfiedBy(
                MemberRecord::fromIdentifier('method#barfoo')
            )
        );
    }
}
