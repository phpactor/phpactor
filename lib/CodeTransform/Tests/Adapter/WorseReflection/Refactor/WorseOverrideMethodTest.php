<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseOverrideMethod;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;

class WorseOverrideMethodTest extends WorseTestCase
{
    /**
     * @dataProvider provideExtractMethod
     */
    public function testOverrideMethod(string $test, string $className, $methodName): void
    {
        [$source, $expected] = $this->sourceExpected(__DIR__ . '/fixtures/' . $test);

        $transformed = $this->overrideMethod($source, $className, $methodName);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    public function provideExtractMethod()
    {
        return [
            'root type as param and return type' => [
                'overrideMethod1.test',
                'ChildClass',
                'barbar'
            ],
            'no params or return type' => [
                'overrideMethod2.test',
                'ChildClass',
                'barbar'
            ],
            'scalar type as param and return type' => [
                'overrideMethod3.test',
                'ChildClass',
                'barbar'
            ],
            'default value' => [
                'overrideMethod4.test',
                'ChildClass',
                'barbar'
            ],
            'parent class with > 1 method' => [
                'overrideMethod5.test',
                'ChildClass',
                'barbar'
            ],
        ];
    }

    public function testClassNoParent(): void
    {
        $this->expectException(TransformException::class);
        $this->overrideMethod('<?php class Foobar {}', 'Foobar', 'foo');
    }

    private function overrideMethod($source, string $className, $methodName)
    {
        $reflector = $this->reflectorForWorkspace($source);
        $factory = new WorseBuilderFactory($reflector);
        $override = new WorseOverrideMethod($reflector, $factory, $this->updater());
        return $override->overrideMethod(SourceCode::fromString($source), $className, $methodName);
    }
}
