<?php

namespace Phpactor\ClassMover\Tests\Adapter\WorseTolerant;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassMover\Domain\MemberFinder;
use Phpactor\ClassMover\Adapter\WorseTolerant\WorseTolerantMemberFinder;
use Phpactor\WorseReflection\ReflectorBuilder;

abstract class WorseTolerantTestCase extends TestCase
{
    protected function createFinder(string $source): MemberFinder
    {
        return new WorseTolerantMemberFinder(
            ReflectorBuilder::create()->addSource($source)->build()
        );
    }
}
