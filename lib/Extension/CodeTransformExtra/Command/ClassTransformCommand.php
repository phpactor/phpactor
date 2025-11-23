<?php

namespace Phpactor\Extension\CodeTransformExtra\Command;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Phpactor;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Extension\CodeTransformExtra\Application\Transformer;
use Webmozart\Glob\Glob;
use RuntimeException;

class ClassTransformCommand extends Command
{
    private Differ $differ;

    public function __construct(
        private Transformer $transformer
    ) {
        parent::__construct();
        $this->differ = new Differ(new UnifiedDiffOutputBuilder());
    }

    public function configure(): void
    {
        $this->setDescription('Apply a transformation to an existing class (path or FQN)');
        $this->addArgument('src', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addOption('transform', 't', InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Tranformations to apply', []);
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not make any changes');
        $this->addOption('diff', null, InputOption::VALUE_NONE, 'Output diff');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pattern = $input->getArgument('src');
        $dryRun = $input->getOption('dry-run');
        $diff = $input->getOption('diff');
        /** @var array $transformations */
        $transformations = $input->getOption('transform');

        $pattern = Phpactor::normalizePath($pattern);

        if (false === Glob::isDynamic($pattern) && false === file_exists($pattern)) {
            throw new RuntimeException(sprintf(
                'File "%s" does not exist',
                $pattern
            ));
        }

        $paths = array_filter(Glob::glob($pattern), function ($path) {
            return is_file($path);
        });

        if (empty($paths)) {
            $output->writeln(sprintf('No files found for pattern "%s"', $pattern));
            return 0;
        }

        $affected = 0;
        foreach ($paths as $path) {
            $existing = SourceCode::fromStringAndPath(file_get_contents($path), $path);
            $transformed = $this->transformer->transform($existing, $transformations);

            $changed = trim($existing->__toString()) != trim($transformed);

            if ($changed) {
                $affected++;
            }

            if ($changed && $diff) {
                $output->writeln($this->differ->diff($existing, $transformed));
            }

            if ($dryRun === false && $changed) {
                $output->writeln($path);
                file_put_contents($path, $transformed);
            }
        }

        $output->writeln(sprintf(
            '%s files affected%s',
            $affected,
            $dryRun ? ' (dry run)' : ''
        ));

        return 0;
    }
}
