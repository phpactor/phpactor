<?php

namespace Phpactor\Extension\SearchExtension\Command;

use Phpactor\Extension\Core\Console\Formatter\Highlight;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Search\Model\Matcher;
use Phpactor\Search\Model\PatternMatch;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\TextDocument\Util\LineAtOffset;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends Command
{
    const ARG_PATTERN = 'pattern';

    private Matcher $matcher;
    private FilesystemRegistry $filesystemRegistry;


    public function __construct(Matcher $matcher, FilesystemRegistry $filesystemRegistry)
    {
        parent::__construct();
        $this->matcher = $matcher;
        $this->filesystemRegistry = $filesystemRegistry;
    }

    public function configure(): void
    {
        $this->addArgument(self::ARG_PATTERN, InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $pattern = $input->getArgument(self::ARG_PATTERN);
        $filesystem = $this->filesystemRegistry->get('git');
        foreach ($filesystem->fileList()->phpFiles() as $file) {
            $document = TextDocumentBuilder::fromUri($file->__toString())->build();
            $matches = $this->matcher->match($document, $pattern);
            if (count($matches) === 0) {
                continue;
            }

            $output->writeln(sprintf('<fg=cyan>%s</>:', $file->path()));
            $edits = [];

            foreach ($matches as $match) {
                $startLineCol = LineCol::fromByteOffset($document, $match->range()->start());
                $endLineCol = LineCol::fromByteOffset($document, $match->range()->end());
                $output->writeln(sprintf(
                    '(%d:%d,%d:%d) %s',
                    $startLineCol->line(),
                    $startLineCol->col(),
                    $endLineCol->line(),
                    $endLineCol->col(),
                    Highlight::highlightAtCol(
                        LineAtOffset::lineAtByteOffset($document, ByteOffset::fromInt($match->range()->start()->toInt() + 1)),
                        substr($document->__toString(),$match->range()->start()->toInt(), $match->range()->length()), $startLineCol->col() - 1, true
                    )
                ));
            }
            $output->writeln('');
        }
        return 0;
    }
}
