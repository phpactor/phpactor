<?php

namespace Phpactor\Extension\Symfony\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Extension\Symfony\Model\SymfonyContainerInspector;
use Phpactor\Extension\Symfony\Model\SymfonyTemplateCache;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Reflector;

final class SymfonyCompletor implements TolerantCompletor
{
    /**
    * @var array<TolerantCompletor>
    */
    private array $completors = [];

    public function __construct(
        Reflector $reflector,
        SymfonyContainerInspector $inspector,
        QueryClient $queryClient,
        SymfonyTemplateCache $templatePathCache,
        ClientApi $clientApi,
        Workspace $workspace,
    ) {
        $this->completors = [
            new SymfonyContainerCompletor($reflector, $inspector),
            new SymfonyFormTypeCompletor($reflector, $queryClient, $clientApi, $workspace),
            new SymfonyTemplateCompletor($reflector, $templatePathCache),
        ];
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        foreach ($this->completors as $completor) {
            yield from $completor->complete($node, $source, $offset);
        }

        return true;
    }

}
