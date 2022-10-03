<?php

namespace Phpactor\ClassMover\Tests\Unit\Domain\Model;

use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use PHPUnit\Framework\TestCase;

class ClassMemberQueryTest extends TestCase
{
    public function testOnlyConstants(): void
    {
        $query = ClassMemberQuery::create()->onlyConstants();
        $this->assertEquals(ClassMemberQuery::TYPE_CONSTANT, $query->type());
    }

    public function testOnlyMethods(): void
    {
        $query = ClassMemberQuery::create()->onlyMethods();
        $this->assertEquals(ClassMemberQuery::TYPE_METHOD, $query->type());
    }

    public function testOnlyProperties(): void
    {
        $query = ClassMemberQuery::create()->onlyProperties();
        $this->assertEquals(ClassMemberQuery::TYPE_PROPERTY, $query->type());
    }

    public function testHasType(): void
    {
        $query = ClassMemberQuery::create();
        $this->assertFalse($query->hasType());

        $query = $query->onlyConstants();
        $this->assertTrue($query->hasType());
    }
}
