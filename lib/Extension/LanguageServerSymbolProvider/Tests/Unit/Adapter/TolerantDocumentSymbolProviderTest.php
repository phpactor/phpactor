<?php

namespace Phpactor\Extension\LanguageServerSymbolProvider\Tests\Unit\Adapter;

use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerSymbolProvider\Adapter\TolerantDocumentSymbolProvider;

class TolerantDocumentSymbolProviderTest extends TestCase
{
    public function testPropertiesInTraits(): void
    {
        $provider = new TolerantDocumentSymbolProvider(new Parser());

        $nodes = $provider->provideFor('<?php trait Foo { public $foo; }');

        $this->assertCount(1, $nodes);
        $this->assertIsArray($nodes[0]->children);
        $this->assertCount(1, $nodes[0]->children);
    }

    public function testMethodsInTraits(): void
    {
        $provider = new TolerantDocumentSymbolProvider(new Parser());

        $nodes = $provider->provideFor('<?php trait Foo { public function foo() {} }');

        $this->assertCount(1, $nodes);
        $this->assertIsArray($nodes[0]->children);
        $this->assertCount(1, $nodes[0]->children);
    }
}
