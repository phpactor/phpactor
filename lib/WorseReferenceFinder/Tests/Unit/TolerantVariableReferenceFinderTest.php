<?php

namespace Phpactor\WorseReferenceFinder\Tests\Unit;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReferenceFinder\TolerantVariableReferenceFinder;
use function iterator_to_array;
use Exception;

class TolerantVariableReferenceFinderTest extends TestCase
{
    /**
    * @dataProvider provideReferences
    */
    public function testReferences(string $source, bool $includeDefinition = false): void
    {
        $uri = 'file:///root/testDoc';
        list($source, $selectionOffset, $expectedReferences) = $this->offsetsFromSource($source, $uri);
        $document = TextDocumentBuilder::create($source)
            ->uri($uri)
            ->language('php')
            ->build();
        
        $finder = new TolerantVariableReferenceFinder(new Parser(), $includeDefinition);
        $actualReferences = iterator_to_array($finder->findReferences($document, ByteOffset::fromInt($selectionOffset)), false);

        $this->assertEquals($expectedReferences, $actualReferences);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideReferences(): Generator
    {
        yield 'not on variable' => [
            '<?php $var1 = <>5;'
        ];

        yield 'basic' => [
            '<?php $v<>ar1 = 5; $var2 = <sr>$var1 + 10;'
        ];

        yield 'dynamic name' => [
            '<?php $v<>ar1 = 5; echo $<sr>$var1;',
        ];

        yield 'function argument' => [
            '<?php $v<>ar1 = 5; func(<sr>$var1);',
        ];

        yield 'function argument with type' => [
            '<?php $v<>ar1 = 5; func(string <sr>$var1);',
        ];

        yield 'global statement' => [
            '<?php $v<>ar1 = 5; global <sr>$var1;',
        ];

        yield 'dynamic property name' => [
            '<?php $v<>ar1 = 5; $obj-><sr>$var1 = 5;',
        ];

        yield 'dynamic property name (braced)' => [
            '<?php $v<>ar1 = 5; $obj->{<sr>$var1} = 5;',
        ];

        yield 'dynamic method name' => [
            '<?php $v<>ar1 = 5; $obj-><sr>$var1(5);',
        ];

        yield 'dynamic method name (braced)' => [
            '<?php $v<>ar1 = 5; $obj->{<sr>$var1}(5);',
        ];

        yield 'dynamic class name' => [
            '<?php $v<>ar1 = 5; $obj = new <sr>$var1();',
        ];

        yield 'embedded string' => [
            '<?php $v<>ar1 = 5; $str = "Text {<sr>$var1} more text";',
        ];

        yield 'exception in a catch clause' => [
            '<?php try { $a = 5; } catch (Exception <sr>$<>e) { echo <sr>$e->getMessage(); }',
            true
        ];

        yield 'scope: exception in a catch clause (skip other with same names)' => [
            '<?php try { $b = 4; } catch (Exception $e) { echo $e->getMessage(); }  try { $a = 5; } catch (Exception <sr>$e) { echo <sr>$<>e->getMessage(); }',
            true
        ];

        yield 'scope: anonymous function: argument' => [
            '<?php $v<>ar1 = 5; $func = function($var1) { };',
        ];

        yield 'scope: anonymous function: use statement' => [
            '<?php $v<>ar1 = 5; $func = function() use (<sr>$var1) { };',
        ];

        yield 'scope: anonymous function: inside' => [
            '<?php $v<>ar1 = 5; $func = function() use (<sr>$var1) { $var2 = <sr>$var1; };',
        ];

        yield 'scope: anonymous function: inside selection' => [
            '<?php $var1 = 5; $func = function() use (<sr>$var1) { $var2 = <sr>$v<>ar1; };',
        ];

        yield 'scope: anonymous function: only inside' => [
            '<?php $var1 = 2; $func = function() { $v<>ar1 = 5; $var2 = <sr>$var1 + 10; };',
        ];

        yield 'scope: anonymous function: only outside' => [
            '<?php $va<>r1 = 2; $func = function() { $var1 = 5; $var2 = $var1 + 10; }; $var2 = <sr>$var1 / 4;',
        ];

        yield 'scope: inside class method' => [
            '<?php class C1 { function M1($var1) { <sr>$v<>ar1 = 5; $var2 = <sr>$var1 + 10; } }',
        ];

        yield 'scope: inside class method: select argument' => [
            '<?php class C1 { function M1($va<>r1) { <sr>$var1 = 5; $var2 = <sr>$var1 + 10; } }',
        ];

        yield 'scope: inside class method: select argument definition' => [
            '<?php class C1 { function M1(string <sr>$va<>r1) { <sr>$var1 = 5; $var2 = <sr>$var1 + 10; } }',
            true
        ];

        yield 'scope: inside class method: inside anonumous function + use, click inside' => [
            '<?php class C1 { function M1() { $var1 = 10; $f = function() use (<sr>$var1) { <sr>$v<>ar1 = 5; $var2 = <sr>$var1 + 10; } } }',
        ];

        yield 'scope: inside class method: inside anonumous function + use, click outside' => [
            '<?php class C1 { function M1() { $v<>ar1 = 10; $f = function() use (<sr>$var1) { <sr>$var1 = 5; $var2 = <sr>$var1 + 10; } } }',
        ];

        yield 'scope: inside class method: inside anonumous function + use, click in use' => [
            '<?php class C1 { function M1() { $var1 = 10; $f = function() use (<sr>$v<>ar1) { <sr>$var1 = 5; $var2 = <sr>$var1 + 10; } } }',
        ];

        yield 'scope: inside class method: inside anonumous function (no use), click inside' => [
            '<?php class C1 { function M1() { $var1 = 10; $f = function($var1) { <sr>$v<>ar1 = 5; $var2 = <sr>$var1 + 10; } } }',
        ];

        yield 'scope: inside class method: inside anonumous function (no use), click outside' => [
            '<?php class C1 { function M1() { $v<>ar1 = 10; $f = function($var1) { $var1 = 5; $var2 = $var1 + 10; } } }',
        ];

        yield 'scope: inside class method: inside anonumous class method' => [
            '<?php '.
                'class C1 { function M1() { '.
                '$var = 1;'.
                '$c = new class { function IM() { $v<>ar = 1; } } '.
                ' } }',
        ];

        yield 'skip: static property access' => [
            '<?php '.
                'class C1 { static $prop1; function M1() { self::$pro<>p1 = 5; $var4 = self::$prop1; } }',
        ];

        yield 'skip: static property declaration' => [
            '<?php '.
                'class C1 { static $pr<>op1; function M1() { self::$prop1 = 5; $var4 = self::$prop1; } }',
        ];

        yield 'skip: instance property declaration' => [
            '<?php '.
                'class C1 { public $pr<>op1; function M1() { $this->prop1 = 5; $var4 = $this->prop1; } }',
        ];
    }
    /** @return mixed[] */
    private static function offsetsFromSource(string $source, ?string $uri): array
    {
        $textDocumentUri = $uri !== null ? TextDocumentUri::fromString($uri) : null;
        $results = preg_split('/(<>|<sr>)/u', $source, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        $referenceLocations = [];
        $selectionOffset = null;

        if (!is_array($results)) {
            throw new Exception('No selection.');
        }

        $newSource = '';
        $offset = 0;
        foreach ($results as $result) {
            if ($result == '<>') {
                $selectionOffset = $offset;
                continue;
            }
            
            if ($result == '<sr>') {
                $referenceLocations[] = PotentialLocation::surely(
                    new Location($textDocumentUri, ByteOffset::fromInt($offset))
                );
                continue;
            }
            
            $newSource .= $result;
            $offset += mb_strlen($result);
        }
        
        return [$newSource, $selectionOffset, $referenceLocations];
    }
}
