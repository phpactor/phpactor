<?php

declare(strict_types=1);

namespace Phpactor\Extension\PHPUnit\Tests\Unit\MemberContextResolver;

use Phpactor\Tests\IntegrationTestCase;
use Phpactor\Extension\PHPUnit\MemberContextResolver\AssertMemberContextResolver;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Core\Inference\Walker\TestAssertWalker;

class AssertMemberContextResolverTest extends IntegrationTestCase
{
    public function testResolveClassString(): void
    {
        $this->resolve(
            <<<'EOT'
                <?php

                class SomeTest extends PHPUnit\Framework\Assert {
                    public function test(): void {
                        $obj = '';
                        wrAssertType('SomeClass', $obj);
                        $this->assertInstanceOf('SomeClass', $obj);
                    }
                }
                EOT
        );
    }

    public function resolve(string $sourceCode): void
    {
        $sourceCode = TextDocumentBuilder::fromUnknown($sourceCode);
        $reflector = ReflectorBuilder::create()
            ->addFrameWalker(new TestAssertWalker($this))
            ->addMemberContextResolver(new AssertMemberContextResolver())
            ->build();

        $reflector->reflectOffset($sourceCode, 10);
    }
}
