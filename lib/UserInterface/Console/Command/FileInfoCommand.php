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
use Phpactor\Application\FileInfo;

class FileInfoCommand extends Command
{
    private $infoForOffset;

    public function __construct(
        FileInfo $infoForOffset
    ) {
        parent::__construct();
        $this->infoForOffset = $infoForOffset;
    }

    public function configure()
    {
        $this->setName('file:info');
        $this->setDescription('Return information about given file');
        $this->addArgument('path', InputArgument::REQUIRED, 'Source path or FQN');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $info = $this->infoForOffset->infoForFile(
            $input->getArgument('path')
        );

        $format = $input->getOption('format');

        switch ($format) {
            case Handler\FormatHandler::FORMAT_JSON:
                $output->write(json_encode($info));
                return;
            case Handler\FormatHandler::FORMAT_CONSOLE:
                return $this->outputConsole($output, $info);
        }

        throw new \InvalidArgumentException(sprintf(
            'Invalid format "%s", known formats: "%s"',
            $format, implode('", "', Handler\FormatHandler::VALID_FORMATS)
        ));
    }

    private function outputConsole(OutputInterface $output, array $info)
    {
        foreach ($info as $key => $value) {
            $output->writeln(sprintf(
                '<comment>%s</comment>:%s', $key, $value
            ));
        }
    }
}
