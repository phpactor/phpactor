<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\TextDocument\TextDocument;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Error;

final class TolerantAstProvider implements AstProvider
{
    public function __construct(
        private Parser $parser = new Parser(),
        private LoggerInterface $logger = new NullLogger(
        )
    ) {
    }

    public function get(TextDocument $document): SourceFileNode
    {
        $start = microtime(true);
        $node = $this->parser->parseSourceFile(
            $document->__toString(),
            $document->uri()?->__toString(),
        );
        $this->logger->info(sprintf(
            'PARS %s %s',
            number_format(microtime(true) - $start, 6),
            $document->uri()?->__toString() ?? '<anonymous>' . (new Error())->getTraceAsString(),
        ));

        return $node;
    }

    public function parseString(string $string): SourceFileNode
    {
        return $this->parser->parseSourceFile($string);
    }
}
