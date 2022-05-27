<?php

namespace Phpactor\Extension\Console;

use InvalidArgumentException;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleExtension implements Extension
{
    public const TAG_COMMAND = 'console.command';
    public const SERVICE_COMMAND_LOADER = 'console.command_loader';
    public const SERVICE_OUTPUT = 'console.output';
    public const SERVICE_INPUT = 'console.input';
    private const PARAM_VERBOSITY = 'console.verbosity';
    private const PARAM_DECORATED = 'console.decorated';


    public function load(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_COMMAND_LOADER, function (Container $container) {
            $map = [];
            foreach ($container->getServiceIdsForTag(self::TAG_COMMAND) as $commandId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new InvalidArgumentException(sprintf(
                        'Command with service ID "%s" must have the "name" attribute',
                        $commandId
                    ));
                }

                $map[$attrs['name']] = $commandId;
            }

            return new PhpactorCommandLoader($container, $map);
        });

        $container->register(self::SERVICE_OUTPUT, function (Container $container) {
            return new ConsoleOutput(
                $container->getParameter(self::PARAM_VERBOSITY),
                $container->getParameter(self::PARAM_DECORATED)
            );
        });

        $container->register(self::SERVICE_INPUT, function (Container $container) {
            return new ArgvInput();
        });
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_VERBOSITY => OutputInterface::VERBOSITY_NORMAL,
            self::PARAM_DECORATED => null,
        ]);
        $schema->setDescriptions([
            self::PARAM_VERBOSITY => 'Verbosity level',
            self::PARAM_DECORATED => 'Whether to decorate messages (null for auto-guessing)',
        ]);
        $schema->setEnums([
            self::PARAM_VERBOSITY => [
                OutputInterface::VERBOSITY_QUIET,
                OutputInterface::VERBOSITY_NORMAL,
                OutputInterface::VERBOSITY_VERBOSE,
                OutputInterface::VERBOSITY_VERY_VERBOSE,
                OutputInterface::VERBOSITY_DEBUG,
            ],
            self::PARAM_DECORATED => [
                true,
                false,
                null
            ]
        ]);
    }
}
