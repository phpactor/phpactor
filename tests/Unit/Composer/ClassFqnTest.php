<?php

namespace Phpactor\Tests\Unit\Composer;

use Phpactor\Composer\ClassFqn;

class ClassFqnTest extends \PHPUnit_Framework_TestCase
{
    private $classFqn;

    /**
     * @dataProvider provideClassFqn
     */
    public function testClassFqn($classFqn, $expectedNamespace, $expectedShortName)
    {
        $classFqn = ClassFqn::fromString($classFqn);
        $this->assertEquals($expectedNamespace, $classFqn->getNamespace(), 'Namespace is correct');
        $this->assertEquals($expectedShortName, $classFqn->getShortName(), 'Shortname is correct');
    }

    public function provideClassFqn()
    {
        return [
            [
                'Foobar\Barfoo\BazBar',
                'Foobar\Barfoo',
                'BazBar'
            ],
            [
                'BazBar',
                '',
                'BazBar'
            ],
            [
                '\Foobar\BazBar',
                'Foobar',
                'BazBar'
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Trailing slash detected
     */
    public function testShouldThrowExceptionIfTrailingSlashInClassName()
    {
        ClassFqn::fromString('Foobar\\');
    }
}
