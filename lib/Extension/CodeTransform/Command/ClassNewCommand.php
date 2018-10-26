<?php

namespace Phpactor\Extension\CodeTransform\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\QuestionHelper;
use Phpactor\Extension\CodeTransform\Application\ClassNew;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Extension\CodeTransform\Application\Exception\FileAlreadyExists;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;

class ClassNewCommand extends Command
{
    /**
     * @var DumperRegistry
     */
    private $dumperRegistry;

    /**
     * @var ClassNew
     */
    private $classNew;

    public function __construct(
        ClassNew $classNew,
        DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
        $this->dumperRegistry = $dumperRegistry;
        $this->classNew = $classNew;
    }

    public function configure()
    {
        $this->setName('class:new');
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
            return $this->listGenerators($input, $output);
        }

        $out = $this->process($input, $output);
        $this->dumperRegistry->get($input->getOption('format'))->dump($output, $out);
    }

    private function process(InputInterface $input, OutputInterface $output)
    {
        $src = $input->getArgument('src');
        $variant = $input->getOption('variant');
        $response = [
            'src' => $src,
            'path' => null,
            'exists' => false,
        ];

        try {
            $response['path'] = $this->classNew->generate($src, $variant, $input->getOption('force'));
        } catch (FileAlreadyExists $exception) {
            $questionHelper = new QuestionHelper();
            $question = new ConfirmationQuestion('<question>File already exists, overwrite? [y/n]</>', false);

            if (false === $questionHelper->ask($input, $output, $question)) {
                $response['exists'] = true;
                return $response;
            }

            $filePath = $this->classNew->generate($src, $variant, true);
            $response['path'] = $filePath;
        }

        return $response;
    }

    private function listGenerators(InputInterface $input, OutputInterface $output)
    {
        $dumper = $this->dumperRegistry->get($input->getOption('format'));
        $dumper->dump($output, $this->classNew->availableGenerators());
    }
}
