<?php

namespace Phpactor\Extension\Core\Command;

use JsonException;
use Phpactor\Configurator\ConfigManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigSetCommand extends Command
{
    const ARG_KEY = 'key';
    const ARG_VALUE = 'value';
    const OPT_DELETE = 'delete';

    public function __construct(private ConfigManipulator $manipulator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Set a config value');
        $this->addArgument(self::ARG_KEY, InputArgument::REQUIRED, 'Config key to set');
        $this->addArgument(self::ARG_VALUE, InputArgument::OPTIONAL, 'Value (JSON encoded) if omitted, key will be removed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $this->manipulator->initialize();

        $key = $input->getArgument(self::ARG_KEY);
        $value = $input->getArgument(self::ARG_VALUE);
        if ($value !== null) {
            try {
                $this->manipulator->set($key, json_decode($value, true, 512, JSON_THROW_ON_ERROR));
            } catch (JsonException) {
                $output->writeln(sprintf('<error>Could not decode JSON value: %s</>', $value));
                return 1;
            }
            $output->writeln(sprintf('<info>Updated:</> %s', $this->manipulator->configPath()));
            $output->writeln(sprintf('%s = %s', $key, $value));
            return 0;
        }

        $this->manipulator->delete($key);
        $output->writeln(sprintf('<info>Removed key:</> %s', $key));
        return 0;
    }
}
