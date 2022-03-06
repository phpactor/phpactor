<?php

namespace Phpactor\Extension\ExtensionManager\Rpc;

use Exception;
use Phpactor\Extension\ExtensionManager\Model\ExtensionState;
use Phpactor\Extension\ExtensionManager\Service\InstallerService;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\ErrorResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\MapResolver\Resolver;

class ExtensionInstallHandler extends AbstractHandler implements Handler
{
    const PARAM_EXTENSION_NAME = 'extension_name';
    const NAME = 'extension_install';


    /**
     * @var InstallerService
     */
    private $installer;

    public function __construct(InstallerService $installer)
    {
        $this->installer = $installer;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_EXTENSION_NAME => null,
        ]);
    }

    public function handle(array $arguments)
    {
        if (null === $arguments[self::PARAM_EXTENSION_NAME]) {
            $this->requireInput($this->createTextInput());
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        try {
            $this->installer->requireExtensions([ $arguments[self::PARAM_EXTENSION_NAME] ]);
        } catch (Exception $e) {
            return ErrorResponse::fromMessageAndDetails(
                'Failed to install extension, try running `phpactor extension:install` from the command line',
                $e->getMessage()
            );
        };

        return EchoResponse::fromMessage(sprintf('Extension "%s" installed', $arguments[self::PARAM_EXTENSION_NAME]));
    }

    private function formatState(ExtensionState $extensionState)
    {
        if ($extensionState->isInstalled()) {
            return '✔';
        }
        return ' ';
    }

    private function createTextInput(string $default = '')
    {
        $textInput = TextInput::fromNameLabelAndDefault(
            self::PARAM_EXTENSION_NAME,
            'Extension name:',
            $default
        );
        return $textInput;
    }
}
