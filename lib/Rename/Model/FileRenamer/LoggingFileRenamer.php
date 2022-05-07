<?php

namespace Phpactor\Rename\Model\FileRenamer;

use Amp\Promise;
use Phpactor\Rename\Model\FileRenamer;
use Phpactor\TextDocument\TextDocumentUri;
use Psr\Log\LoggerInterface;
use function Amp\call;

class LoggingFileRenamer implements FileRenamer
{
    private LoggerInterface $logger;

    private FileRenamer $innerRenamer;

    public function __construct(FileRenamer $innerRenamer, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->innerRenamer = $innerRenamer;
    }

    
    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Promise
    {
        return call(function () use ($from, $to) {
            $result = $this->innerRenamer->renameFile($from, $to);
            $this->logger->debug(sprintf(
                'Moved file "%s" to "%s"',
                $from->__toString(),
                $to->__toString()
            ));
            return $result;
        });
    }
}
