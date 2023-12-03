<?php

namespace Phpactor\Completion\Core;

use Closure;
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

    private string|Closure|null $shortDescription;

    private string $label;

    private string|Closure|null $documentation;

    /**
     * @param null|string|Closure $documentation
     * @param null|string|Closure $shortDescription
     */
    private function __construct(
        private string $name,
        private ?string $type = null,
        $shortDescription = null,
        private ?string $nameImport = null,
        ?string $label = null,
        $documentation = null,
        private ?Range $range = null,
        private ?string $snippet = null,
        private ?int $priority = null,
        private ?string $fqn = null,
    ) {
        $this->shortDescription = $shortDescription;
        $this->label = $label ?: $name;
        $this->documentation = $documentation;
    }

    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * @param array{
     *   short_description?:string|null|Closure,
     *   documentation?:string|null|Closure,
     *   type?:string|null,
     *   class_import?:string|null,
     *   name_import?:string|null,
     *   fqn?:string|null,
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
            'fqn' => null,
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
            $options['fqn'],
        );
    }

    /**
     * @return array<string,mixed>
     */
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
            'fqn' => $this->fqn,
            'range' => $this->range ? $this->range->toArray() : null,

            // removed
            'info' => '',
        ];
    }

    public function type(): ?string
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
        if ($this->shortDescription instanceof Closure) {
            $shortDescription = $this->shortDescription;
            return $shortDescription();
        }
        return $this->shortDescription;
    }

    /**
     * @param string|Closure $description
     */
    public function withShortDescription($description): self
    {
        $clone = clone $this;
        $clone->shortDescription = $description;

        return $clone;
    }

    /**
     * Return the FQN if the name should be imported.
     */
    public function classImport(): ?string
    {
        return $this->type() === self::TYPE_CLASS ? $this->nameImport : null;
    }

    /**
     * Fully qualified name of suggestion, if applicable
     */
    public function fqn(): ?string
    {
        return $this->fqn ?? $this->nameImport;
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
        if ($this->documentation instanceof Closure) {
            $documentation = $this->documentation;
            return $documentation();
        }
        return $this->documentation;
    }

    public function hasDocumentation(): bool
    {
        return !empty($this->documentation);
    }

    public function priority(): ?int
    {
        return $this->priority;
    }

    public function withLabel(string $label): self
    {
        $new = clone $this;
        $new->label = $label;
        return $new;
    }

    /**
     * @param null|string|Closure $documentation
     */
    public function withDocumentation($documentation): self
    {
        $new = clone $this;
        $new->documentation = $documentation;
        return $new;
    }
}
