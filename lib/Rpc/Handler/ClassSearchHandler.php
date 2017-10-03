<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassSearch;
use Phpactor\Container\SourceCodeFilesystemExtension;
use Phpactor\Rpc\Editor\ReturnAction;
use Phpactor\Rpc\Editor\ReturnOption;
use Phpactor\Rpc\Editor\ReturnChoiceAction;
use Phpactor\Rpc\Editor\EchoAction;

class ClassSearchHandler implements Handler
{
    const NAME = 'class_search';
    const SHORT_NAME = 'short_name';


    /**
     * @var ClassSearch
     */
    private $classSearch;

    /**
     * @var string
     */
    private $defaultFilesystem;

    public function __construct(
        ClassSearch $classSearch,
        string $defaultFilesystem = SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER
    ) {
        $this->classSearch = $classSearch;
        $this->defaultFilesystem = $defaultFilesystem;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::SHORT_NAME => null,
        ];
    }

    public function handle(array $arguments)
    {
        $results = $this->classSearch->classSearch(
            $this->defaultFilesystem,
            $arguments[self::SHORT_NAME]
        );

        if (count($results) === 0) {
            return EchoAction::fromMessage(sprintf('No classes found with short name "%s"', $arguments[self::SHORT_NAME]));
        }

        if (count($results) === 1) {
            $result = reset($results);
            return ReturnAction::fromValue($result);
        }

        $options = [];
        foreach ($results as $result) {
            $options[] = ReturnOption::fromNameAndValue(
                $result['class'],
                $result
            );
        }

        return ReturnChoiceAction::fromOptions($options);
    }
}

