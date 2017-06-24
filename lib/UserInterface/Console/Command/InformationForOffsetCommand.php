<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassInformationForOffsetr\ClassInformationForOffsetr;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Phpactor;
use Phpactor\UserInterface\Console\Logger\SymfonyConsoleInformationForOffsetLogger;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Application\InformationForOffset\InformationForOffset;

class InformationForOffsetCommand extends Command
{
    const FORMAT_JSON = 'json';
    const FORMAT_CONSOLE = 'console';

    const VALID_FORMATS = [
        self::FORMAT_JSON,
        self::FORMAT_CONSOLE
    ];

    private $infoForOffset;

    public function __construct(
        InformationForOffset $infoForOffset
    ) {
        parent::__construct();
        $this->infoForOffset = $infoForOffset;
    }

    public function configure()
    {
        $this->setName('offset:info');
        $this->setDescription('Return information about given file at the given offset');
        $this->addArgument('path', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addArgument('offset', InputArgument::REQUIRED, 'Destination path or FQN');
        $this->addOption('format', null, InputOption::VALUE_REQUIRED, sprintf(
            'Output format: "%s"', implode('", "', self::VALID_FORMATS)
        ), 'console');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $info = $this->infoForOffset->infoForOffset(
            $input->getArgument('path'),
            $input->getArgument('offset')
        );

        $format = $input->getOption('format');

        switch ($format) {
            case self::FORMAT_JSON:
                $output->write(json_encode($info));
                return;
            case self::FORMAT_CONSOLE:
                return $this->outputConsole($output, $info);
        }

        throw new \InvalidArgumentException(sprintf(
            'Invalid format "%s", known formats: "%s"',
            $format, implode('", "', self::VALID_FORMATS)
        ));
    }

    private function outputConsole(OutputInterface $output, array $info)
    {
        foreach ($info as $key => $value) {
            $output->writeln(sprintf(
                '%s: %s', $key, $value
            ));
        }
    }
}
