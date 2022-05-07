<?php

namespace Phpactor\Rename\Tests\Integration\Adapter\ReferenceFinder;

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
use Phpactor\WorseReflection\Reflector;

class MemberRenamerTest extends RenamerTestCase
{

    /**
     * @return Generator<array<int,mixed>>
     */
    public function provideRename(): Generator
    {
        yield [
            'member_renamer/method_declaration',
            function (Reflector $reflector, Renamer $renamer): Generator {
                $reflection = $reflector->reflectClass('ClassOne');
                $method = $reflection->methods()->get('foobar');
                return $renamer->rename(
                    $reflection->sourceCode(),
                    $method->nameRange()->start(),
                    'newName'
                );
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
