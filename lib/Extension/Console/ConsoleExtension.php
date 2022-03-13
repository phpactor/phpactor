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
    const TAG_COMMAND = 'console.command';
    const SERVICE_COMMAND_LOADER = 'console.command_loader';
    const SERVICE_OUTPUT = 'console.output';
    const SERVICE_INPUT = 'console.input';

    
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
                $container->getParameter('console.verbosity'),
                $container->getParameter('console.decorated')
            );
        });

        $container->register(self::SERVICE_INPUT, function (Container $container) {
            return new ArgvInput();
        });
    }

    
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            'console.verbosity' => OutputInterface::VERBOSITY_NORMAL,
            'console.decorated' => null,
        ]);
    }
}
