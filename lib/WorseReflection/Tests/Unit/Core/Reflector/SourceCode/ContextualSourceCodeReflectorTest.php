<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Reflector\SourceCode;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\Reflector\SourceCode\ContextualSourceCodeReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\PhpUnit\ProphecyTrait;

class ContextualSourceCodeReflectorTest extends TestCase
{
    use ProphecyTrait;
    const TEST_SOURCE_CODE = '<?php echo "hello";';
    const TEST_OFFSET = 1;

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
        self::assertEquals(2, $this->reflector->reflectClassesIn(TextDocumentBuilder::fromUnknown('<?php class One{} class Two{}'))->count());
    }

    public function testReflectOffset(): void
    {
        $offset = $this->reflector->reflectOffset(TextDocumentBuilder::fromUnknown(self::TEST_SOURCE_CODE), self::TEST_OFFSET);
        self::assertInstanceOf(ReflectionOffset::class, $offset);
    }

    public function testReflectMethodCall(): void
    {
        $call = $this->reflector->reflectMethodCall(TextDocumentBuilder::fromUnknown('<?php class One { function bar() {} } $f = new One();$f->bar();'), 59);
        self::assertInstanceOf(ReflectionMethodCall::class, $call);
    }

    /**
    * @dataProvider provideReflectAnonymousClass
    */
    public function testReflectAnonymousClass(string $sourceCode, int $expectedCount): void
    {
        $result = $this->reflector->reflectClassesIn(TextDocumentBuilder::fromUnknown($sourceCode));

        self::assertCount($expectedCount, $result);
    }

    /**
     * @return Generator<string,array{string,int}>
     */
    public function provideReflectAnonymousClass(): Generator
    {
        yield 'one class' => [
            '<?php $formatter = new class() {};', 1
        ];

        yield 'one class with implements' => [
            '<?php $formatter = new class implements Test () {};', 1
        ];

        yield 'two classes' => [
            '<?php $billow = new class() {}; $formatter = new class() {};', 2
        ];
    }

}
