<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Reflector\SourceCode;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\Reflector\SourceCode\ContextualSourceCodeReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\PhpUnit\ProphecyTrait;

class ContextualSourceCodeReflectorTest extends TestCase
{
    use ProphecyTrait;

    private ContextualSourceCodeReflector $reflector;

    private TemporarySourceLocator $locator;

    public function setUp(): void
    {
        $this->locator = new TemporarySourceLocator(ReflectorBuilder::create()->build());

        $this->reflector = new ContextualSourceCodeReflector(
            ReflectorBuilder::create()->build(),
            $this->locator
        );
    }

    public function testReflectsClassesIn(): void
    {
        self::assertEquals(2, $this->reflector->reflectClassesIn(SourceCode::fromString('<?php class One{} class Two{}'))->count());
    }

    public function testReflectOffset(): void
    {
        $offset = $this->reflector->reflectOffset(SourceCode::fromString('<?php echo "hello";'), 1);
        self::assertInstanceOf(ReflectionOffset::class, $offset);
    }

    public function testReflectMethodCall(): void
    {
        $call = $this->reflector->reflectMethodCall(SourceCode::fromString('<?php class One { function bar() {} } $f = new One();$f->bar();'), 59);
        self::assertInstanceOf(ReflectionMethodCall::class, $call);
    }

    /**
    * @dataProvider provideReflectAnonymousClass
    */
    public function testReflectAnonymousClass(string $sourceCode, int $expectedCount): void
    {
        $result = $this->reflector->reflectClassesIn(SourceCode::fromString($sourceCode));

        self::assertCount($expectedCount, $result);
    }

    /**
     * @return Generator<string,array{string,int}>
     */
    public function provideReflectAnonymousClass(): Generator
    {
        yield 'not anonymous class' => [
            '<?php $formatter = new A();', 0
        ];

        yield 'one class' => [
            '<?php $formatter = new class() {};', 1
        ];

        yield 'one class with implements' => [
            '<?php $formatter = new class implements Test () {};', 1
        ];

        yield 'two classes' => [
            <<<'PHP'
                <?php
                \$billow = new class() {}; 
                \$formatter = new class() {};
                PHP, 2
        ];
    }

}
