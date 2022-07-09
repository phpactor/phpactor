<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Amp\CancellationToken;
use Amp\Promise;
use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class UnusedImportProvider implements DiagnosticProvider
{
    /**
     * @var array<string,bool>
     */
    private array $used = [];
    private ?Node $lastChild = null;


    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): Generator
    {
        if ($node === $this->lastChild) {
            dump('done');
        }

        if (!$node instanceof QualifiedName) {
            return [];
        }
        return;
        yield;
    }
}
