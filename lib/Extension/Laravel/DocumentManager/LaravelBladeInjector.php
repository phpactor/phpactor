<?php

namespace Phpactor\Extension\Laravel\DocumentManager;

use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\Extension\LanguageServerCompletion\Util\DocumentModifier;
use Phpactor\Extension\LanguageServerCompletion\Util\TextDocumentModifierResponse;
use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\Parser\CachedParser;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod as PhpactorReflectionMethod;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Reflector;

class LaravelBladeInjector implements DocumentModifier
{
    public function __construct(
        private LaravelContainerInspector $containerInspector,
        private Reflector $reflector,
        private Workspace $workspace
    ) {
    }

    public function process(string $text, TextDocumentItem $document): ?TextDocumentModifierResponse
    {
        if ($document->languageId === 'blade' || str_ends_with($document->uri, '.blade.php')) {
            $this->containerInspector->viewsData();

            $fileToSearch = str_replace('file://', '', $document->uri);


            $docblock = '';
            $viewsData = $this->containerInspector->viewsData();

            // Try to get the direct file.
            $viewKey = $viewsData['mapping'][$fileToSearch] ?? false;

            $component = null;

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
                    $docblock .= "\$this = new \\{$class}();";
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

                $prefix = '<?php  ' . $docblock . ' ';

                $lines = explode(PHP_EOL, $text);
                $lines[0] = $prefix . $lines[0];
                $text = implode(PHP_EOL, $lines);

                return new TextDocumentModifierResponse($text, mb_strlen($prefix), 'php');
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
