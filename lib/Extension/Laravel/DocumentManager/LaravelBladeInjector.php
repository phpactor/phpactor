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
use Phpactor\WorseReflection\Bridge\TolerantParser\Parser\CachedParser;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod as PhpactorReflectionMethod;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Reflector;

class LaravelBladeInjector implements DocumentModifier
{
    public function __construct(private LaravelContainerInspector $containerInspector, private Reflector $reflector)
    {
    }

    public function process(string $text, TextDocumentItem $document): ?TextDocumentModifierResponse
    {
        if ($document->languageId === 'blade') {
            $this->containerInspector->snippets();

            $fileToSearch = str_replace('file://', '', $document->uri);

            $docblock = '';
            // This is only for livewire.
            foreach ($this->containerInspector->snippets() as $key => $entry) {
                if ($entry['livewire'] ?? false) {
                    foreach ($entry['views'] as $key => $file) {
                        if ($file === $fileToSearch) {
                            $additionalArguments = [];
                            // Easier approach to use a mixin to set the type?
                            if ($class = $entry['class'] ?? false) {
                                $docblock .= "\$this = new \\{$class}();";
                                $additionalArguments = $this->getAdditionalArgumentsForClass($class);
                            }
                            foreach ($additionalArguments as $name => $type) {
                                $var = '$' . $name;
                                $docblock .= "/** @var {$type->toPhpString()} $var */ $var; ";
                            }
                            foreach ($entry['arguments'] as $name => $data) {
                                if ($data['type'] ?? false) {
                                    $type = $data['type'];
                                    if (str_contains($type, '\\')) {
                                        $type = '\\' . $type;
                                    }
                                    $var = '$' . $name;
                                    $docblock .= "/** @var $type $var */ $var; ";
                                }
                            }

                            continue;
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

        return null;
    }

    private function getAdditionalArgumentsForClass(string $class): array
    {
        $members = $this->reflector->reflectClass($class)->ownMembers();

        $parser = new CachedParser();

        $list = [];

        foreach ($members as $member) {
            if ($member->name() === 'render') {
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
