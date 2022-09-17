<?php

namespace Phpactor\Extension\SearchExtension\Command;

use Phpactor\Extension\Core\Console\Formatter\Highlight;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Search\Model\Constraint\TextConstraint;
use Phpactor\Search\Model\Constraint\TypeConstraint;
use Phpactor\Search\Model\TokenConstraints;
use Phpactor\Search\Model\TokenReplacement;
use Phpactor\Search\Model\TokenReplacements;
use Phpactor\Search\Search;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\Util\LineAtOffset;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Path;

class SsrCommand extends Command
{
    const ARG_PATTERN = 'pattern';
    const ARG_PATH = 'path';
    const OPT_TEXT = 'text';
    const OPT_TYPE = 'type';
    const OPT_REPLACE = 'replace';

    private Search $search;
    private FilesystemRegistry $filesystemRegistry;

    public function __construct(Search $search, FilesystemRegistry $filesystemRegistry)
    {
        parent::__construct();
        $this->search = $search;
        $this->filesystemRegistry = $filesystemRegistry;
    }

    public function configure(): void
    {
        $this->setDescription('Structural search and replace');
        $this->addArgument(self::ARG_PATH, InputArgument::REQUIRED);
        $this->addArgument(self::ARG_PATTERN, InputArgument::REQUIRED);
        $this->addOption(self::OPT_TEXT, null, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Filter placeholders by text');
        $this->addOption(self::OPT_TYPE, null, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Filter placeholders by type');
        $this->addOption(self::OPT_REPLACE, null, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Replace placeholder with text');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        assert($output instanceof ConsoleOutput);
        $path = $input->getArgument(self::ARG_PATH);
        $pattern = $input->getArgument(self::ARG_PATTERN);
        $constraints = new TokenConstraints(...array_merge(
            array_map(function (string $constraint) {
                return TextConstraint::fromString($constraint);
            }, (array)$input->getOption(self::OPT_TEXT)),
            array_map(function (string $constraint) {
                return TypeConstraint::fromString($constraint);
            }, (array)$input->getOption(self::OPT_TYPE))
        ));
        $replacements = new TokenReplacements(...array_map(function (string $replacement) {
            return TokenReplacement::fromString($replacement);
        }, (array)$input->getOption(self::OPT_REPLACE)));

        $output->getErrorOutput()->writeln(sprintf('template: %s', $pattern));
        foreach ($constraints as $constraint) {
            $output->getErrorOutput()->writeln(sprintf('  filter: <fg=cyan>%s %s</>', $constraint->placeholder(), $constraint->describe()));
        }
        foreach ($replacements as $replacement) {
            $output->getErrorOutput()->writeln(sprintf(' replace: <fg=cyan>%s</> with <fg=cyan>%s</>', $replacement->placeholder(), $replacement->replacement()));
        }

        $filesystem = $this->filesystemRegistry->get('git');
        $questionHelper = new QuestionHelper();

        foreach ($filesystem->fileList()->phpFiles()->within(
            FilePath::fromString(Path::makeAbsolute((string)$path, (string)getcwd()))
        ) as $file) {
            $document = TextDocumentBuilder::fromUri($file->__toString())->build();

            $matches = $this->search->search($document, $pattern, $constraints);

            if (count($matches) === 0) {
                continue;
            }

            $output->write(sprintf('<fg=cyan>%s</>:', $file->path()));
            $edits = [];

            foreach ($matches as $match) {
                $startLineCol = LineCol::fromByteOffset($document, $match->range()->start());
                $endLineCol = LineCol::fromByteOffset($document, $match->range()->end());

                $output->writeln(str_replace("\n", ' ', sprintf(
                    '(%d:%d,%d:%d) %s',
                    $startLineCol->line(),
                    $startLineCol->col(),
                    $endLineCol->line(),
                    $endLineCol->col(),
                    Highlight::highlightAtCol(
                        LineAtOffset::lineAtByteOffset($document, ByteOffset::fromInt($match->range()->start()->toInt() + 1)),
                        substr($document->__toString(), $match->range()->start()->toInt(), $match->range()->length()),
                        $startLineCol->col() - 1,
                        true
                    )
                )));

            }

            $document = $replacements->applyTo($matches);
            if ($document->__toString() !== $matches->document()->__toString()) {
                $questionHelper->ask(new ConfirmationQuestion(sprintf('Update "%s"?', $document->uri()->__toString())));
                if (false === file_put_contents($document->uri()->path(), $document->__toString())) {
                    throw new RuntimeException(sprintf('Could not update file "%s"', $document->uri()));
                }
            }
        }

        return 0;
    }
}
