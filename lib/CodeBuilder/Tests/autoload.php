<?php

use Microsoft\PhpParser\Node;
use Phpactor\CodeBuilder\Adapter\TolerantParser\NodeQuery;
use SebastianBergmann\Exporter\Exporter;

require __DIR__  . '/../vendor/autoload.php';

function debug_node($node): void
{
    if ($node instanceof Node) {
        $node = new NodeQuery($node);
    }

    if (!$node instanceof NodeQuery) {
        throw new RuntimeException(sprintf(
            'Invalid debug node type "%s"',
            get_class($node)
        ));
    }


    $exporter = new Exporter();
    $lines = [];
    $lines[] = get_class($node->innerNode());
    $lines[] = sprintf(
        'full-start: %s, start: %s, end: %s, start-line: %s, end-line: %s',
        $node->fullStart(),
        $node->start(),
        $node->end(),
        $node->startLineNumber(),
        $node->endLineNumber()
    );
    $lines[] = sprintf('%s', $exporter->export($node->fullText()));

    echo PHP_EOL.implode("\n", $lines).PHP_EOL;
}
