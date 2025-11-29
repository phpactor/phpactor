<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Virtual\StubFileMemberProvider;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;

class StubMemberProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $stubs = [__DIR__ . '/example/model.stub'];
        $reflector = $this->createReflector($stubs);

        $classes = $reflector->reflectClassesIn(
            TextDocumentBuilder::fromUri(__DIR__ . '/example/model.php.test')->build()
        );

        $reflection = $classes->get('Example\Model');
        self::assertEquals('static(Example\Model)|false', $reflection->methods()->get('findOne')->inferredType()->__toString());
    }

    public function testProviderExtended(): void
    {
        $stubs = [__DIR__ . '/example/model.stub'];
        $reflector = $this->createReflector($stubs);

        $classes = $reflector->reflectClassesIn(
            TextDocumentBuilder::fromUri(__DIR__ . '/example/model.php.test')->build()
        );

        $reflection = $classes->get('Example\Blog');
        self::assertEquals(
            'static(Example\Blog)|false',
            $reflection->methods()->get(
                'findOne'
            )->inferredType()->__toString()
        );
    }

    public function testProvideVirtualMethodsFromStubs(): void
    {
        $stubs = [__DIR__ . '/example/model.stub'];
        $reflector = $this->createReflector($stubs);

        $classes = $reflector->reflectClassesIn(
            TextDocumentBuilder::fromUri(__DIR__ . '/example/model.php.test')->build()
        );

        $reflection = $classes->get('Example\Blog');
        self::assertEquals(
            'string',
            $reflection->properties()->get('virtualString')->inferredType()->__toString()
        );
    }

    /**
     * @param string[]  $stubs
     */
    private function createReflector(array $stubs): Reflector
    {
        $reflector = ReflectorBuilder::create()
            ->enableContextualSourceLocation()
            ->addMemberProvider(
                new StubFileMemberProvider($stubs)
            )
            ->build();
        return $reflector;
    }
}
