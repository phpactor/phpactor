<?php

namespace Phpactor\Extension\CodeTransformExtra\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\QuestionHelper;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Extension\CodeTransformExtra\Application\Exception\FileAlreadyExists;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Phpactor\Extension\CodeTransformExtra\Application\ClassInflect;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;

class ClassInflectCommand extends Command
{
    public function __construct(
        private readonly ClassInflect $classInflect,
        private readonly DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Inflect new class from existing class (path or FQN)');
        $this->addArgument('src', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addArgument('dest', InputArgument::REQUIRED, 'Destination path or FQN');
        $this->addArgument('variant', InputOption::VALUE_REQUIRED, 'Type of inflection', 'default');
        $this->addOption('list', null, InputOption::VALUE_NONE, 'List variants');
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Force overwriting');
        FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('list')) {
            return $this->listGenerators($input, $output);
        }

        $out = $this->process($input, $output);
        $this->dumperRegistry->get($input->getOption('format'))->dump($output, $out);

        return 0;
    }

    private function process(InputInterface $input, OutputInterface $output)
    {
        $src = $input->getArgument('src');
        $dest = $input->getArgument('dest');
        $variant = $input->getArgument('variant');
        $response = [
            'src' => $src,
            'dest' => $dest,
            'path' => null,
            'exists' => false,
        ];

        try {
            $response['path'] = $this->classInflect->generateFromExisting($src, $dest, $variant, $input->getOption('force'));
        } catch (FileAlreadyExists) {
            $questionHelper = new QuestionHelper();
            $question = new ConfirmationQuestion('<question>File already exists, overwrite? [y/n]</>', false);

            if (false === $questionHelper->ask($input, $output, $question)) {
                $response['exists'] = true;
                return $response;
            }

            $filePath = $this->classInflect->generateFromExisting($src, $dest, $variant, true);
            $response['path'] = $filePath;
        }

        return $response;
    }

    private function listGenerators(InputInterface $input, OutputInterface $output)
    {
        $dumper = $this->dumperRegistry->get($input->getOption('format'));
        $dumper->dump($output, $this->classInflect->availableGenerators());

        return 0;
    }
}
