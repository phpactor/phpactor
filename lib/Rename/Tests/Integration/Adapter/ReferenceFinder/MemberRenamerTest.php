<?php

namespace Phpactor\Rename\Tests\Integration\Adapter\ReferenceFinder;

use Closure;
use Generator;
use Microsoft\PhpParser\Parser;
use Phpactor\Extension\LanguageServerBridge\TextDocument\FilesystemWorkspaceLocator;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedImplementationFinder;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedReferenceFinder;
use Phpactor\Indexer\Adapter\Tolerant\TolerantIndexBuilder;
use Phpactor\Indexer\IndexAgentBuilder;
use Phpactor\Indexer\Model\IndexBuilder;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Rename\Adapter\ReferenceFinder\MemberRenamer;
use Phpactor\Rename\Model\Renamer;
use Phpactor\Rename\Tests\RenamerTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Reflector;

class MemberRenamerTest extends RenamerTestCase
{

    /**
     * @return Generator<string,array{string,Closure(Reflector,Renamer): Generator,Closure(Reflector): void}>
     */
    public function provideRename(): Generator
    {
        yield 'method declaration' => [
            'member_renamer/method_declaration',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $method = $reflection->methods()->get('foobar');

                return $renamer->rename(
                    $reflection->sourceCode(),
                    $method->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $reflection = $reflector->reflectClass('ClassOne');
                self::assertTrue($reflection->methods()->has('newName'));
            }
        ];

        yield 'method reference' => [
            'member_renamer/method_declaration',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $methodCalls = $reflector->navigate($this->workspace()->getContents('project/ClassTwo.php'))->methodCalls();
                $first = $methodCalls->first();
                assert($first instanceof ReflectionMethodCall);

                return $renamer->rename(
                    TextDocumentBuilder::fromUri($this->workspace()->path('project/ClassTwo.php'))->build(),
                    $first->nameRange()->start(),
                    'newName'
                );
            },
            function (Reflector $reflector): void {
                $methodCalls = $reflector->navigate($this->workspace()->getContents('project/ClassTwo.php'))->methodCalls();
                $first = $methodCalls->first();
                self::assertEquals('newName', $first->name());
            }
        ];
    }

    protected function createRenamer(): Renamer
    {
        $finder = new IndexedReferenceFinder(
            $this->indexAgent->query(),
            $this->reflector
        );
        return new MemberRenamer(
            $finder,
            new FilesystemWorkspaceLocator(),
            new Parser(),
            new IndexedImplementationFinder($this->indexAgent->query(),$this->reflector)
        );
    }
}
