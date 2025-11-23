<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use Closure;
use Generator;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\Exception\ConstantNotFound;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class ConstantReflectorTest extends IntegrationTestCase
{
    #[DataProvider('provideReflectDeclaredConstant')]
    public function testReflectFunction(string $manifest, string $name, Closure $assertion): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);

        $locator = new StubSourceLocator(
            ReflectorBuilder::create()->build(),
            $this->workspace()->path('project'),
            $this->workspace()->path('cache'),
        );
        $reflection = ReflectorBuilder::create()->addLocator($locator)->build()->reflectConstant($name);
        $assertion($reflection);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideReflectDeclaredConstant(): Generator
    {
        yield 'reflect in root namespace' => [
            <<<'EOT'
                // File: project/hello.php
                <?php define('HELLO', 'hello') {}
                EOT
            ,
            'HELLO',
            function (ReflectionDeclaredConstant $constant): void {
                $this->assertEquals('"hello"', $constant->type()->__toString());
            }
        ];

        yield 'fallback to global constant' => [
            <<<'EOT'
                // File: project/global.php
                <?php
                <?php define('HELLO', 'hello') {}
                EOT
            ,
            'Foo\HELLO',
            function (ReflectionDeclaredConstant $constant): void {
                $this->assertEquals('HELLO', $constant->name());
            }
        ];

        yield 'namespaced function' => [
            <<<'EOT'
                // File: project/global.php
                <?php
                <?php define('Foo\HELLO', 'hello') {}
                EOT
            ,
            'Foo\HELLO',
            function (ReflectionDeclaredConstant $function): void {
                $this->assertEquals('Foo\HELLO', $function->name());
            }
        ];
    }

    public function testThrowsExceptionIfFunctionNotFound(): void
    {
        $this->expectException(ConstantNotFound::class);
        $this->createReflector('<?php ')->reflectConstant('hallo');
    }
}
