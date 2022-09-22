<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;

class GenerateDecoratorCommand extends Command
{
    public const NAME = 'generate_decorator';

    private ClientApi $clientApi;

    private Workspace $workspace;

    private Reflector $reflector;

    private Updater $updater;

    public function __construct(
        ClientApi $clientApi,
        Workspace $workspace,
        Reflector $reflector,
        Updater $updater
    ) {
        $this->clientApi = $clientApi;
        $this->workspace = $workspace;
        $this->reflector = $reflector;
        $this->updater = $updater;
    }

    /**
     * @return Promise<ApplyWorkspaceEditResponse>
     */
    public function __invoke(string $uri, string $interfaceFQN): Promise
    {
        $textDocument = $this->workspace->get($uri);
        $source = SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri);

        $textEdits = $this->getTextEdits($source, $interfaceFQN);

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
            $uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)
        ]), 'Generate decoration');
    }

    private function getTextEdits(SourceCode $source, string $interfaceFQN): TextEdits
    {
        $class = $this->reflector->reflectClassesIn($source)->classes()->first();

        $builder = SourceCodeBuilder::create();
        $builder->namespace($class->name()->namespace());
        $classBuilder = $builder->class($class->name()->short());

        $classBuilder->property('inner')->visibility(Visibility::PRIVATE)->type($interfaceFQN);

        $constructor = $classBuilder->method('__construct');
        $constructor->parameter('inner')->type($interfaceFQN);
        $constructor->body()->line('$this->inner = $inner');

        $interface = $this->reflector->reflectInterface($interfaceFQN);
        foreach ($interface->methods() as $interfaceMethod) {
            $method = $classBuilder->method($interfaceMethod->name());

            $method->returnType($interfaceMethod->returnType());
            $method->visibility($interfaceMethod->visibility());

            $this->attachParameters($method, $interfaceMethod);

            $method->body()->line($this->generateMethodBody($interfaceMethod));
        }

        return $this->updater->textEditsFor($builder->build(), Code::fromString((string) $source));
    }

    /**
     * Copying over the method parameters from the interface to the decoration
     */
    private function attachParameters(MethodBuilder $method, ReflectionMethod $interfaceMethod): void
    {
        foreach ($interfaceMethod->parameters() as $interfaceMethodParameter) {
            $method->parameter($interfaceMethodParameter->name())
                   ->type($interfaceMethodParameter->type())
                   ->defaultValue($interfaceMethodParameter->default())
                ;
        }
    }

    /**
     * This method creates the method body which means copying parameters of the interface method to the body of the function.
     * So if the interface contains:
     *
     * function someFunction(string $a, int $b)
     *
     * then the content of the decoration method needs to be
     *
     * $this->inner->someFunction($a, $b);
     */
    private function generateMethodBody(ReflectionMethod $interfaceMethod): string
    {
        $code = '$this->inner->'.$interfaceMethod->name().'(';
        foreach ($interfaceMethod->parameters() as $interfaceMethodParameter) {
            $code .= $interfaceMethodParameter->name().','.PHP_EOL;
        }
        $code .= ')';

        if (!$interfaceMethod->returnType()->isVoid()) {
            $code = 'return '. $code;
        }

        return $code;
    }
}
