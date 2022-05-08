<?php

namespace Phpactor\Completion\Core;

use RuntimeException;

class Suggestion
{
    /**
     * Completion types based on the language server protocol:
     * https://github.com/Microsoft/language-server-protocol/blob/gh-pages/specification.md#completion-request-leftwards_arrow_with_hook
     */
    const TYPE_METHOD = 'method';
    const TYPE_FUNCTION = 'function';
    const TYPE_CONSTRUCTOR = 'constructor';
    const TYPE_FIELD = 'field';
    const TYPE_VARIABLE = 'variable';
    const TYPE_CLASS = 'class';
    const TYPE_INTERFACE = 'interface';
    const TYPE_MODULE = 'module';
    const TYPE_PROPERTY = 'property';
    const TYPE_UNIT = 'unit';
    const TYPE_VALUE = 'value';
    const TYPE_ENUM = 'enum';
    const TYPE_KEYWORD = 'keyword';
    const TYPE_SNIPPET = 'snippet';
    const TYPE_COLOR = 'color';
    const TYPE_FILE = 'file';
    const TYPE_REFERENCE = 'reference';
    const TYPE_CONSTANT = 'constant';
    const PRIORITY_HIGH = 64;
    const PRIORITY_MEDIUM = 127;
    const PRIORITY_LOW = 255;

    private ?string $type;

    private string $name;

    private ?string $shortDescription;

    private string $label;

    private ?Range $range;

    private ?string $documentation;

    private ?string $snippet;

    private ?string $nameImport;

    private ?int $priority;

    private function __construct(
        string $name,
        ?string $type = null,
        ?string $shortDescription = null,
        ?string $nameImport = null,
        ?string $label = null,
        ?string $documentation = null,
        ?Range $range = null,
        ?string $snippet = null,
        ?int $priority = null
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->shortDescription = $shortDescription;
        $this->label = $label ?: $name;
        $this->range = $range;
        $this->documentation = $documentation;
        $this->snippet = $snippet;
        $this->nameImport = $nameImport;
        $this->priority = $priority;
    }

    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * @param array{
     *   short_description?:string|null,
     *   documentation?:string|null,
     *   type?:string|null,
     *   class_import?:string|null,
     *   name_import?:string|null,
     *   label?:string|null,
     *   range?:Range|null,
     *   snippet?:string|null,
     *   priority?:int|null
     * } $options
     */
    public static function createWithOptions(string $name, array $options): self
    {
        $defaults = [
            'short_description' => '',
            'documentation' => '',
            'type' => null,
            'class_import' => null,
            'name_import' => null,
            'label' => null,
            'range' => null,
            'snippet' => null,
            'priority' => null,
        ];

        if ($diff = array_diff(array_keys($options), array_keys($defaults))) {
            throw new RuntimeException(sprintf(
                'Invalid options for suggestion: "%s" valid options: "%s"',
                implode('", "', $diff),
                implode('", "', array_keys($defaults))
            ));
        }

        $options = array_merge($defaults, $options);

        return new self(
            $name,
            $options['type'],
            $options['short_description'],
            $options['name_import'] ? $options['name_import'] : $options['class_import'],
            $options['label'],
            $options['documentation'],
            $options['range'],
            $options['snippet'],
            $options['priority'],
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type(),
            'name' => $this->name(),
            'snippet' => $this->snippet(),
            'label' => $this->label(),
            'short_description' => $this->shortDescription(),
            'documentation' => $this->documentation(),
            'class_import' => $this->type() === self::TYPE_CLASS && $this->nameImport ? $this->nameImport : null,
            'name_import' => $this->nameImport,
            'range' => $this->range ? $this->range->toArray() : null,

            // deprecated: in favour of short_description, to be removed
            // after 0.10.0
            'info' => $this->shortDescription(),
        ];
    }

    /**
     * @return string|null
     */
    public function type()
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function snippet(): ?string
    {
        return $this->snippet;
    }

    public function shortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function withShortDescription(string $description): self
    {
        $clone = clone $this;
        $clone->shortDescription = $description;

        return $clone;
    }

    /**
     * @deprecated Use nameImport instead
     */
    public function classImport(): ?string
    {
        return $this->type() === self::TYPE_CLASS ? $this->nameImport : null;
    }

    public function nameImport(): ?string
    {
        return $this->nameImport;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function range(): ?Range
    {
        return $this->range;
    }

    public function documentation(): ?string
    {
        return $this->documentation;
    }

    public function priority(): ?int
    {
        return $this->priority;
    }
}
