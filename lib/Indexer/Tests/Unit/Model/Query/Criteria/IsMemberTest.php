<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;

class IsMemberTest extends TestCase
{
    public function testIsFunction(): void
    {
        self::assertFalse(Criteria::isMember()->isSatisfiedBy(ClassRecord::fromName('foobar')));
        self::assertTrue(Criteria::isMember()->isSatisfiedBy(MemberRecord::fromIdentifier('method#barfoo')));
    }
}
