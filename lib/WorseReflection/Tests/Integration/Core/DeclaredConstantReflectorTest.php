<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use Closure;
use Generator;
use Phpactor\WorseReflection\Core\Exception\FunctionNotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class DeclaredConstantReflectorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectDeclaredConstant
     */
    public function testReflectFunction(string $manifest, string $name, Closure $assertion): void
    {
        $this->workspace()->loadManifest($manifest);

        $locator = new StubSourceLocator(
            ReflectorBuilder::create()->build(),
            $this->workspace()->path('project'),
            $this->workspace()->path('cache'),
        );
        $reflection = ReflectorBuilder::create()->addLocator($locator)->build()->reflectFunction($name);
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
            function (ReflectionFunction $function): void {
                $this->assertEquals('"hello"', $function->type()->__toString());
            }
        ];

        return;

        yield 'fallback to global function' => [
            <<<'EOT'
                // File: project/global.php
                <?php
                function hello() {}
                EOT
            ,
            'Foo\hello',
            function (ReflectionFunction $function): void {
                $this->assertEquals('hello', $function->name());
            }
        ];

        yield 'namespaced function' => [
            <<<'EOT'
                // File: project/global.php
                <?php
                namespace Foo;
                function hello() {}
                EOT
            ,
            'Foo\hello',
            function (ReflectionFunction $function): void {
                $this->assertEquals('hello', $function->name());
            }
        ];
    }

    public function testThrowsExceptionIfFunctionNotFound(): void
    {
        $this->expectException(FunctionNotFound::class);
        $this->createReflector('<?php ')->reflectFunction('hallo');
    }
}
