<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Application\OffsetInfo;
use Phpactor\UserInterface\Console\Dumper\DumperRegistry;
use Phpactor\Application\OffsetAction;

class OffsetActionCommand extends Command
{
    /**
     * @var OffsetAction
     */
    private $offsetAction;

    /**
     * @var DumperRegistry
     */
    private $dumperRegistry;

    public function __construct(
        OffsetAction $offsetAction,
        DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
        $this->offsetAction = $offsetAction;
        $this->dumperRegistry = $dumperRegistry;
    }

    public function configure()
    {
        $this->setName('offset:action');
        $this->setDescription('List and/or perform actions on an offset');
        $this->addArgument('path', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addArgument('offset', InputArgument::REQUIRED, 'Destination path or FQN');
        $this->addArgumnet('action', InputArgument::OPTIONAL, 'Action to perform');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->processResult($input);

        $format = $input->getOption('format');
        $this->dumperRegistry->get($format)->dump($output, $info);
    }

    private function processResult(InputInterface $input, string $path, int $offset, string $action = null)
    {
        if (null === $action) {
            $choices = $this->offsetAction->choicesFromOffset($path, $offset);

            if (false === $input->isInteractive()) {
                return $choices;
            }

            throw new \Exception('TODO');
        }

        $this->offsetAction->performAction($path, $offset, $action);
    }
}
