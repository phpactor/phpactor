<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Application\ClassCopy;
use Phpactor\Rpc\Editor\OpenFileAction;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Application\Logger\NullLogger;

class ClassCopyHandler extends AbstractHandler
{
    const NAME = 'copy_class';
    const PARAM_SOURCE_PATH = 'source_path';
    const PARAM_DEST_PATH = 'dest_path';


    /**
     * @var ClassCopy
     */
    private $classCopy;

    public function __construct(ClassCopy $classCopy)
    {
        $this->classCopy = $classCopy;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAM_SOURCE_PATH => null,
            self::PARAM_DEST_PATH => null,
        ];
    }

    public function handle(array $arguments)
    {
        $this->requireArgument(self::PARAM_DEST_PATH, TextInput::fromNameLabelAndDefault(
            self::PARAM_DEST_PATH,
            'Copy to: ',
            $arguments[self::PARAM_SOURCE_PATH]
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $this->classCopy->copy(new NullLogger(), $arguments[self::PARAM_SOURCE_PATH], $arguments[self::PARAM_DEST_PATH]);

        return OpenFileAction::fromPath($arguments[self::PARAM_DEST_PATH]);
    }
}
