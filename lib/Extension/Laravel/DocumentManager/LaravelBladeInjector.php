<?php

namespace Phpactor\Extension\Laravel\DocumentManager;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\StringLiteral;
use Monolog\Logger;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCompletion\Util\DocumentModifier;
use Phpactor\Extension\LanguageServerCompletion\Util\TextDocumentModifierResponse;
use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\Extension\Laravel\Adapter\SimplifiedBladeCompiler;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\Parser\CachedParser;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod as PhpactorReflectionMethod;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Reflector;

class LaravelBladeInjector implements DocumentModifier
{
    public function __construct(
        private LaravelContainerInspector $containerInspector,
        private Reflector $reflector,
        private Workspace $workspace,
        private Logger $logger
    ) {
    }

    public function process(
        string $text,
        TextDocumentItem $document,
        Position $position
    ): ?TextDocumentModifierResponse {
        if ($document->languageId === 'blade' || str_ends_with($document->uri, '.blade.php')) {
            $this->containerInspector->viewsData();

            $fileToSearch = str_replace('file://', '', $document->uri);

            $docblock = '';
            $viewsData = $this->containerInspector->viewsData();

            $intOffset = PositionConverter::positionToByteOffset($position, $text)->toInt();

            $fs = new Filesystem();
            $compiler = new SimplifiedBladeCompiler($fs, sys_get_temp_dir());
            $compiler->withoutComponentTags();

            $separator = 'PHPALSPLIT';

            $startContentLenghtOriginal = mb_strlen(substr($text, 0, $intOffset));
            $text = substr_replace($text, $separator, $intOffset, 0);

            $text = $compiler->compileString($text);

            // Split it again and remove the string.
            [$start, $end] = explode($separator, $text);

            $lengthDiff = mb_strlen($start) - $startContentLenghtOriginal;

            $text = $start.$end;

            // Try to get the direct file.
            $viewKey = $viewsData['mapping'][$fileToSearch] ?? false;

            $component = null;
            file_put_contents('/tmp/phpactor-laravel.log', json_encode($viewsData['blade']), FILE_APPEND);

            if (!$viewKey) {
                // Try to load it from the regular views.
                foreach ($viewsData['blade'] as $bladeView) {
                    if ($bladeView['file'] === $fileToSearch) {
                        $component = $bladeView;
                        break;
                    }
                }
            } else {
                $component = $viewsData['livewire'][$viewKey] ?? $viewsData['blade'][$viewKey] ?? false;
            }

            if ($component) {
                // Add the arguments from the component.
                foreach ($component['arguments'] as $name => $data) {
                    if ($data['type'] ?? false) {
                        $type = $data['type'];
                        if (str_contains($type, '\\')) {
                            $type = '\\' . $type;
                        }
                        $var = '$' . $name;
                        $docblock .= "/** @var $type $var */ $var; ";
                    }
                }

                // If there is a class include it.
                if ($class = $component['class'] ?? false) {
                    $docblock .= "\$this = new {$class}();";
                    $additionalArguments = $this->getAdditionalArgumentsForClass($class);
                    foreach ($additionalArguments as $name => $type) {
                        $var = '$' . $name;
                        $docblock .= "/** @var {$type->__toString()} $var */ $var; ";
                    }
                } elseif ($classUsedFrom = $component['used_in'][0] ?? null) {
                    $fileName = $classUsedFrom['file'];
                    $offset = $classUsedFrom['pos'];
                    // Find the method name.
                    $doc = TextDocumentBuilder::fromUri($fileName, 'php');
                    $classes = $this->reflector->reflectClassesIn($doc->build());

                    if ($class = $classes->firstOrNull()) {
                        foreach ($class->ownMembers()->methods() as $method) {
                            if (
                                $method->position()->start()->toInt() < $offset &&
                                $method->position()->end()->toInt() > $offset
                            ) {
                                // @todo: Change this to use the same class instance so it does not have to refetch it.
                                $args = $this->getAdditionalArgumentsForClass($class->name()->__toString(), $method->name());
                                foreach ($args as $name => $type) {
                                    $var = '$' . $name;
                                    $docblock .= "/** @var {$type->__toString()} $var */ $var; ";
                                }
                                break;
                            }
                        }
                    }
                }

                $prefix = '<?php  ' . $docblock . ' ?>';

                /* $this->logger->log('error', $prefix); */

                $lines = explode(PHP_EOL, $text);
                $lines[0] = $prefix . $lines[0];
                $text = implode(PHP_EOL, $lines);

                $inc = mb_strlen($prefix) + $lengthDiff;
                
                return new TextDocumentModifierResponse($text, $inc, 'php');
            }
        }
        return null;
    }

    private function getAdditionalArgumentsForClass(string $class, string $functionName = 'render'): array
    {
        $members = $this->reflector->reflectClass($class)->ownMembers();

        $parser = new CachedParser();

        $list = [];

        foreach ($members as $member) {
            if ($member->name() === $functionName) {
                if ($member instanceof PhpactorReflectionMethod) {
                    $parsedClass = $parser->parseSourceFile($member->class()->sourceCode()->__toString());
                    $method = $parsedClass->getDescendantNodeAtPosition($member->position()->start()->toInt());

                    /** @var ReturnStatement|null $return */
                    $return = $method->getFirstDescendantNode(ReturnStatement::class);
                    if ($return) {
                        // Find the 'View'
                        $viewArray = $return->getFirstDescendantNode(CallExpression::class)
                            ?->getFirstDescendantNode(ArrayCreationExpression::class)
                            ?->getFirstDescendantNode(ArrayElementList::class);

                        if ($viewArray) {
                            foreach ($viewArray->getChildNodes() as $childNode) {
                                if ($childNode instanceof ArrayElement) {
                                    $i = 0;
                                    $key = null;
                                    foreach ($childNode->getChildNodes() as $leftRight) {
                                        if ($i === 0) {
                                            // Left hand side.
                                            if ($leftRight instanceof StringLiteral) {
                                                $key = $leftRight->getStringContentsText();
                                            }
                                        } elseif ($i === 1 && $key) {
                                            // This is dump but a starting point.
                                            if (str_contains($leftRight->getText(), '==')) {
                                                $list[$key] = new BooleanType();
                                            } elseif ($leftRight instanceof CallExpression) {
                                                if ($last = $leftRight->callableExpression) {
                                                    $reflected = $this->reflector->reflectOffset($member->class()->sourceCode(), $last->getEndPosition());
                                                    $list[$key] = $reflected->nodeContext()->type();
                                                }
                                            } else {
                                                $reflected = $this->reflector->reflectOffset($member->class()->sourceCode(), $leftRight->getEndPosition());
                                                $list[$key] = $reflected->nodeContext()->type();
                                            }
                                        }
                                        $i++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $list;
    }
}
