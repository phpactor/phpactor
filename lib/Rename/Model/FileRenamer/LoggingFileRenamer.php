<?php

namespace Phpactor\Rename\Model\FileRenamer;

use Generator;
use Phpactor\Rename\Model\FileRenamer;
use Phpactor\TextDocument\TextDocumentUri;
use Psr\Log\LoggerInterface;

class LoggingFileRenamer implements FileRenamer
{
    public function __construct(private FileRenamer $innerRenamer, private LoggerInterface $logger)
    {
    }


    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Generator
    {
        $rename = $this->innerRenamer->renameFile($from, $to);

        yield from $rename;

        $this->logger->debug(sprintf(
            'Moved file "%s" to "%s"',
            $from->__toString(),
            $to->__toString()
        ));

        return $rename->getReturn();
    }
}
