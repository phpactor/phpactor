<?php

namespace Phpactor;

use InvalidArgumentException;
use Phpactor\Extension\Core\Console\PhpactorCommandLoader;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Phpactor\Container\Container;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputOption;
use PackageVersions\Versions;
use Phpactor\Exension\Logger\LoggingExtension;

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
            if ($input->hasOption('format') && $input->getOption('format')) {
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

        $map = [];
        foreach ($this->container->getServiceIdsForTag('ui.console.command') as $commandId => $attrs) {
            if (!isset($attrs['name'])) {
                throw new InvalidArgumentException(sprintf(
                    'Command with service ID "%s" must have the "name" attribute',
                    $commandId
                ));
            }

            $map[$attrs['name']] = $commandId;
        }

        $commandLoader = new PhpactorCommandLoader($this->container, $map);
        $this->setCommandLoader($commandLoader);
    }
}
