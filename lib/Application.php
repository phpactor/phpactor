<?php

namespace Phpactor;

use PackageVersions\Versions;
use Phpactor\Cast\Cast;
use Phpactor\Extension\Logger\Formatter\PrettyFormatter;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Phpactor\Container\Container;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Exception;
use Throwable;

class Application extends SymfonyApplication
{
    private Container $container;

    public function __construct(private string $vendorDir, private string $phpactorBin)
    {
        parent::__construct('Phpactor', Cast::toString(Versions::getVersion('phpactor/phpactor')));
    }

    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        $this->initialize($input, $output);
        $this->setCatchExceptions(false);

        if ($output->isVerbose()) {
            $handler = new StreamHandler(STDERR);
            $handler->setFormatter($this->container->get(PrettyFormatter::class));
            $this->container->get(LoggingExtension::SERVICE_LOGGER)->pushHandler($handler);
        }

        $formatter = $output->getFormatter();
        $formatter->setStyle('highlight', new OutputFormatterStyle('red', null, [ 'bold' ]));
        $formatter->setStyle('diff-add', new OutputFormatterStyle('green', null, [  ]));
        $formatter->setStyle('diff-remove', new OutputFormatterStyle('red', null, [  ]));

        try {
            return parent::doRun($input, $output);
        } catch (Exception $e) {
            if (
                $input->hasArgument('command')
                && ($command = $input->getArgument('command'))
                && $command !== 'list'
                && $input->hasOption('format')
                && $input->getOption('format')
            ) {
                /** @var string $format */
                $format = $input->getOption('format');

                return $this->handleException($output, $format, $e);
            }

            if ($output instanceof ConsoleOutputInterface) {
                $this->renderThrowable($e, $output->getErrorOutput());
            }

            return 255;
        }
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('working-dir', 'd', InputOption::VALUE_REQUIRED, 'Working directory'));

        return $definition;
    }

    private function handleException(OutputInterface $output, string $dumper, Exception $e): int
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

    /**
     * @return array<string, string>
    */
    private function serializeException(Throwable $e): array
    {
        return [
            'class' => get_class($e),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ];
    }

    private function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->container = Phpactor::boot($input, $output, $this->vendorDir, $this->phpactorBin);

        $this->setCommandLoader($this->container->get(ConsoleExtension::SERVICE_COMMAND_LOADER));
    }
}
