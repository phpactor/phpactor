<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassReflector;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Phpactor;
use Phpactor\UserInterface\Console\Logger\SymfonyConsoleCopyLogger;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Phpactor\UserInterface\Console\Prompt\Prompt;
use Symfony\Component\Console\Helper\Table;

class ClassReflectorCommand extends Command
{
    /**
     * @var ClassReflector
     */
    private $reflector;

    public function __construct(
        ClassReflector $reflector
    ) {
        parent::__construct();
        $this->reflector = $reflector;
    }

    public function configure()
    {
        $this->setName('class:reflect');
        $this->setDescription('Reflect a given class (file or FQN)');
        $this->addArgument('name', InputArgument::REQUIRED, 'Source path or FQN');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $reflection = $this->reflector->reflect($input->getArgument('name'));

        $table = new Table($output);
        $table->addRow([ '<info>Short</>', (string) $reflection['class_name']]);
        $table->addRow([ '<info>Namespace</>', (string) $reflection['class_namespace']]);
        $table->addRow([ '<info>FQN</>', (string) $reflection['class']]);
        $table->addRow([ '<info>Methods:</>', '' ]);
        foreach ($reflection['methods'] as $method) {
            $header = sprintf(
                '%s %s(%s)',
                $method['visibility'],
                '<comment>' . $method['name'] . '</>',
                implode(', ', array_map(function ($parameter) {
                    return 
                        ($parameter['has_type'] ? $parameter['type'] . ' ' : '') .
                        '$' . $parameter['name'] .
                        ($parameter['has_default'] ? ' = ' . var_export($parameter['default']) : '')
                    ;
                }, $method['parameters']))
            );
            $table->addRow([ '', $header ]);
        }
        $table->addRow([ '<info>Properties:</>', '' ]);
        foreach ($reflection['properties'] as $property) {
            $table->addRow([ '', sprintf('%s <comment>$%s</>', $property['visibility'], $property['name'])]);
        }
        $table->render();

    }
}
