<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Virtual\StubFileMemberProvider;
use Phpactor\WorseReflection\ReflectorBuilder;

class StubMemberProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $reflector = ReflectorBuilder::create()
            ->enableContextualSourceLocation()
            ->addMemberProvider(
                new StubFileMemberProvider([
                    __DIR__ . '/example/model.stub',
                ])
            )
            ->build();

        $classes = $reflector->reflectClassesIn(
            TextDocumentBuilder::fromUri(__DIR__ . '/example/model.php.test')->build()
        );

        $reflection = $classes->get('Example\Model');
        self::assertEquals('static(Example\Model)|false', $reflection->methods()->get('findOne')->inferredType()->__toString());
    }

    public function testProviderExtended(): void
    {
        $reflector = ReflectorBuilder::create()
            ->enableContextualSourceLocation()
            ->addMemberProvider(
                new StubFileMemberProvider([
                    __DIR__ . '/example/model.stub',
                ])
            )
            ->build();

        $classes = $reflector->reflectClassesIn(
            TextDocumentBuilder::fromUri(__DIR__ . '/example/model.php.test')->build()
        );

        $reflection = $classes->get('Example\Blog');
        self::assertEquals(
            'static(Example\Model)|false',
            $reflection->methods()->get(
                'findOne'
            )->inferredType()->__toString()
        );
    }
}
