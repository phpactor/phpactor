<?php

namespace Phpactor\ClassMover\Tests\Adapter\TolerantParser;

use PHPUnit\Framework\Attributes\TestDox;
use Phpactor\ClassMover\Adapter\TolerantParser\TolerantClassFinder;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;

class TolerantClassFinderTest extends TestCase
{
    #[TestDox('It finds all class references.')]
    public function testFind(): void
    {
        $tolerantRefFinder = new TolerantClassFinder();
        $source = TextDocumentBuilder::fromUri(__DIR__ . '/examples/Example1.php')->build();
        $names = iterator_to_array($tolerantRefFinder->findIn($source));


        $this->assertCount(8, $names);

        $this->assertEquals('Acme\\Foobar\\Warble', $names[0]->__toString());
        $this->assertEquals('Acme\\Foobar\\Barfoo', $names[1]->__toString());
        $this->assertEquals('Acme\\Barfoo', $names[2]->__toString());
        $this->assertEquals('Acme\\Hello', $names[3]->__toString());
        $this->assertEquals('Acme\\Foobar\\Warble', $names[4]->__toString());
        $this->assertEquals('Acme\\Demo', $names[5]->__toString());
        $this->assertEquals('Acme\\Foobar\\Barfoo', $names[6]->__toString());
        $this->assertEquals('Acme\\Foobar\\Barfoo', $names[7]->__toString());
    }
}
