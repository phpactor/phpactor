<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassReferences;
use Phpactor\Container\SourceCodeFilesystemExtension;
use Phpactor\Rpc\Editor\ReturnAction;
use Phpactor\Rpc\Editor\ReturnOption;
use Phpactor\Rpc\Editor\ReturnChoiceAction;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\FileReferencesAction;
use Phpactor\Rpc\Editor\StackAction;

class ClassReferencesHandler implements Handler
{
    /**
     * @var ClassReferences
     */
    private $classReferences;

    /**
     * @var string
     */
    private $defaultFilesystem;

    public function __construct(
        ClassReferences $classReferences,
        string $defaultFilesystem = SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER
    ) {
        $this->classReferences = $classReferences;
        $this->defaultFilesystem = $defaultFilesystem;
    }

    public function name(): string
    {
        return 'class_references';
    }

    public function defaultParameters(): array
    {
        return [
            'class' => null,
        ];
    }

    public function handle(array $arguments)
    {
        $results = $this->classReferences->findReferences(
            $this->defaultFilesystem,
            $arguments['class']
        );

        $results = $results['references'];

        if (count($results) === 0) {
            return EchoAction::fromMessage('No references found');
        }

        $count = array_reduce($results, function ($count, $result) {
            $count += count($result['references']);
            return $count;
        }, 0);

        return StackAction::fromActions([
            EchoAction::fromMessage(sprintf('Found %s literal references to class "%s"', $count, $arguments['class'])),
            FileReferencesAction::fromArray($results)
        ]);
    }
}

