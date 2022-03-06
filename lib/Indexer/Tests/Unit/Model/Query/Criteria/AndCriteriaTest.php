<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Query\Criteria\FalseCriteria;
use Phpactor\Indexer\Model\Query\Criteria\TrueCriteria;
use Phpactor\Indexer\Model\Record\ClassRecord;

class AndCriteriaTest extends TestCase
{
    public function testAll(): void
    {
        self::assertTrue(Criteria::and(
            new TrueCriteria(),
            new TrueCriteria(),
            new TrueCriteria()
        )->isSatisfiedBy(ClassRecord::fromName('foo')));
    }

    public function testNotAllTrue(): void
    {
        self::assertFalse(Criteria::and(
            new TrueCriteria(),
            new FalseCriteria(),
            new TrueCriteria()
        )->isSatisfiedBy(ClassRecord::fromName('foo')));
    }
}
