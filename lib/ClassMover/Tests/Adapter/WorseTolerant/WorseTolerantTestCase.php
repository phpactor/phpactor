<?php

namespace Phpactor\ClassMover\Tests\Adapter\WorseTolerant;

use Phpactor\ClassMover\Adapter\WorseTolerant\WorseTolerantMemberFinder;
use Phpactor\ClassMover\Domain\MemberFinder;
use Phpactor\WorseReflection\ReflectorBuilder;
use PHPUnit\Framework\TestCase;

abstract class WorseTolerantTestCase extends TestCase
{
    protected function createFinder(string $source): MemberFinder
    {
        return new WorseTolerantMemberFinder(
            ReflectorBuilder::create()->addSource($source)->build()
        );
    }
}
