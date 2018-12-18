<?php

namespace Phpactor;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Phpactor\Container\Container;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputOption;
use PackageVersions\Versions;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Console\ConsoleExtension;

class Application extends SymfonyApplication
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $vendorDir;

    public function __construct(string $vendorDir)
    {
        parent::__construct('Phpactor', Versions::getVersion('phpactor/phpactor'));
        $this->vendorDir = $vendorDir;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->initialize($input);
        $this->setCatchExceptions(false);

        if ($output->isVerbose()) {
            $this->container->get(LoggingExtension::SERVICE_LOGGER)->pushHandler(new StreamHandler(STDERR));
        }

        $formatter = $output->getFormatter();
        $formatter->setStyle('highlight', new OutputFormatterStyle('red', null, [ 'bold' ]));
        $formatter->setStyle('diff-add', new OutputFormatterStyle('green', null, [  ]));
        $formatter->setStyle('diff-remove', new OutputFormatterStyle('red', null, [  ]));

        try {
            return parent::doRun($input, $output);
        } catch (\Exception $e) {
            if (
                $input->hasArgument('command')
                && ($command = $input->getArgument('command'))
                && $command !== 'list'
                && $input->hasOption('format')
                && $input->getOption('format')
            ) {
                return $this->handleException($output, $input->getOption('format'), $e);
            }

            if ($output instanceof ConsoleOutputInterface) {
                $this->renderException($e, $output->getErrorOutput());
            }

            return 255;
        }
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('working-dir', 'd', InputOption::VALUE_REQUIRED, 'Working directory'));

        return $definition;
    }

    private function handleException(OutputInterface $output, string $dumper, \Exception $e)
    {
        $errors = [
            'error' => $this->serializeException($e),
            'previous' => [
            ],
        ];

        $this->container->get('logging.logger')->error($e->getMessage());

        while ($e = $e->getPrevious()) {
            $errors['previous'][] = $this->serializeException($e);
        }

        $this->container->get('console.dumper_registry')->get($dumper)->dump($output, $errors);

        return 64;
    }

    private function serializeException(\Exception $e)
    {
        return [
            'class' => get_class($e),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ];
    }

    private function initialize(InputInterface $input)
    {
        $this->container = Phpactor::boot($input, $this->vendorDir);

        $this->setCommandLoader($this->container->get(ConsoleExtension::SERVICE_COMMAND_LOADER));
    }
}
