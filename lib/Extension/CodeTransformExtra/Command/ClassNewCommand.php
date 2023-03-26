<?php

namespace Phpactor\Extension\CodeTransformExtra\Command;

use Phpactor\CodeTransform\Domain\SourceCode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\QuestionHelper;
use Phpactor\Extension\CodeTransformExtra\Application\ClassNew;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Extension\CodeTransformExtra\Application\Exception\FileAlreadyExists;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;

class ClassNewCommand extends Command
{
    public function __construct(
        private ClassNew $classNew,
        private DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Create new class (path or FQN)');
        $this->addArgument('src', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addOption('variant', null, InputOption::VALUE_REQUIRED, 'Variant', 'default');
        $this->addOption('list', null, InputOption::VALUE_NONE, 'List variants');
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Force overwriting');
        FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('list')) {
            $this->listGenerators($input, $output);
            return 0;
        }

        $out = $this->process($input, $output);
        $this->dumperRegistry->get($input->getOption('format'))->dump($output, $out);

        return 0;
    }

    private function process(InputInterface $input, OutputInterface $output)
    {
        $src = $input->getArgument('src');
        $variant = $input->getOption('variant');

        try {
            $sourceCode = $this->generateSourceCode($src, $variant, $input, $output);
        } catch (FileAlreadyExists) {
            return [
                'src' => $src,
                'path' => null,
                'exists' => true,
            ];
        }

        return [
            'src' => $src,
            'path' => $sourceCode->uri()->path(),
            'exists' => false,
        ];
    }

    private function listGenerators(InputInterface $input, OutputInterface $output): void
    {
        $dumper = $this->dumperRegistry->get($input->getOption('format'));
        $dumper->dump($output, $this->classNew->availableGenerators());
    }

    private function generateSourceCode(string $src, string $variant, InputInterface $input, OutputInterface $output): SourceCode
    {
        try {
            return $this->classNew->generate($src, $variant, $input->getOption('force'));
        } catch (FileAlreadyExists $exception) {
            $questionHelper = new QuestionHelper();
            $question = new ConfirmationQuestion('<question>File already exists, overwrite? [y/n]</>', false);

            if (false === $questionHelper->ask($input, $output, $question)) {
                throw $exception;
            }

            return $this->classNew->generate($src, $variant, true);
        }
    }
}
